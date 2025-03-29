<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edwiser RemUI - Utility Class
 *
 * @package   theme_remui
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learningpath;

use Exception;
use moodle_url;
use stdClass;
use user_picture;
use html_writer;
use context_course;
use context_coursecat;
use context_system;
/**
 * catalogue Utility class
 */
class catalogue_utility {
    /**
     * Returns a list of course categories that the current user has permission to view.
     *
     * The function first retrieves all course categories from the database, and then filters the list to only include
     * categories that the current user has the 'moodle/category:viewcourselist' capability for.
     *
     * If the current user is an administrator or has the 'moodle/category:viewhiddencategories' capability, the function
     * will return all course categories, including hidden ones.
     *
     * @return array An associative array of course categories, where the keys are the category IDs and the values are the
     *               category objects.
     */
    public static function get_categories_list() {
        global $DB, $USER;

        $systemcontext = context_system::instance();
        $categories = $DB->get_records('course_categories',array('visible'=>1));

        if(catalogue_utility::check_user_admin_cap() || has_capability('moodle/category:viewhiddencategories', $systemcontext, $USER->id)){
            $categories = $DB->get_records('course_categories');
        }

        $filteredcategories = array();
        foreach ($categories as $category) {
            $categorycontext = context_coursecat::instance($category->id);
            if (has_capability('moodle/category:viewcourselist', $categorycontext)) {
                $filteredcategories[$category->id] = $category;
            }
        }

        return $filteredcategories;
    }

    public static function get_catalogue_categories_list() {
        global $DB, $USER;

        $systemcontext = context_system::instance();
        $catalogue_categories = $DB->get_records('local_catalogue_categories' ,array('status'=>1));

        $filteredcategories = array();
        foreach ($catalogue_categories as $category) {
            $filteredcategories[$category->id] = $category;
        }

        return $filteredcategories;
    }

    /*
     * Returns course categories menu array context.
     * @param $contextmenu -> $primarymenu['moremenu']
     */
    public static function get_coursecategory_menu($contextmenu) {
        $categories = catalogue_utility::get_categories_list();

        $mainarr = [];
        $coursecategorytext = get_config('theme_remui', 'coursecategoriestext');
        $mainarr['text'] = $coursecategorytext == "" ? get_string('coursecategories', 'theme_remui') : $coursecategorytext;
        $mainarr['key'] = 'coursecat';
        $mainarr['url'] = "#";
        $mainarr['children'] = [];
        $mainarr['classes'] = "catselector-menu";
        $mainarr['sort'] = "catselector-menu";

        $html = catalogue_utility::generateCategoryStructure($categories);

        $mainarr['haschildren'] = true;
        $mainarr['children'] = false;
        $mainarr['categorytreedesign'] = $html;
        if (isset($mainarr['haschildren']) && $mainarr['haschildren']) {
            // To add recent menu at end $contextmenu['moremenu']['nodearray'][] = $mainarr.
            // To add recent menu at end $contextmenu['mobileprimarynav'][] = $mainarr.

            // To add recent menu at start.
            array_unshift($contextmenu['moremenu']['nodearray'], $mainarr);
            array_unshift($contextmenu['mobileprimarynav'], $mainarr);
        }
        return $contextmenu;
    }

    public static function generateCategoryStructure($categories)
    {
        global $CFG;
        // sort by sortorder
        usort($categories, function ($a, $b) {
            return $a->sortorder - $b->sortorder;
        });

        $categoryTree = catalogue_utility::buildCategoryTree($categories, 0);
        $html = '<div class="category-wrapper container d-flex flex-column">';
        $html .= '<div class="menu-wrapper">
                        <ul class="m-0 p-pl-5">
                            <li><a href="#" data-cat-id="0" data-category="All Categories" class="category-catalogue-link ellipsis">'.get_string('allcourescattext', 'theme_remui').'</a></li>
                        </ul>
                  </div>';
        $html .= catalogue_utility::generateHTML($categoryTree);
        $html .= '</div>';

        return $html;
    }

    public static function buildCategoryTree($categories, $parentId)
    {
        $tree = array();

        foreach ($categories as $category) {
            if ($category->parent == $parentId) {
                $subcategory = catalogue_utility::buildCategoryTree($categories, $category->id);
                if (!empty($subcategory)) {
                    $category->children = $subcategory;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }

    public static function generateHTML($categoryTree, $html = '')
    {
        global $CFG;
        foreach ($categoryTree as $category) {
            $uniquenumber = hexdec(uniqid());
            $hasChildren = !empty($category->children);
            $html .= '<div class="menu-wrapper">';

            if ($hasChildren) {
                $html .= '<div class="menu-heading d-flex flex-gap-2 w-100">
                            <div class="toggle-btn d-flex justify-content-center align-items-center collapsed" data-toggle="collapse" data-target="#collapse' . $uniquenumber . '" aria-controls="collapseTwo">
                                <span class="expande-icon edw-icon edw-icon-Down-Arrow" title=""></span>
                                <span class="collaps-icon edw-icon edw-icon-UpArrow" title=""></span>
                            </div>
                            <a href="#" data-cat-id="'.$category->id.'" data-category="'.$category->name.'" class="category-catalogue-link ellipsis catvisibility-'.$category->visible.' ">'.format_text($category->name, FORMAT_HTML).'</a>
                            <i class="hidden-label edw-icon edw-icon-Hide p-mt-0d5 catvisibility-'.$category->visible.'" aria-hidden="true"></i>
                        </div>';
                $html .= '<div id="collapse' .$uniquenumber. '" class="collapse">
                            <div class="menu-body">';
            } else {
                $html .= '<ul class="m-0 p-pl-5">
                                <li><div class="d-flex flex-gap-2 w-100">
                                <a href="#" data-cat-id="'.$category->id.'" data-category="'.$category->name.'" class="category-catalogue-link ellipsis catvisibility-'.$category->visible.' ">'.format_text($category->name, FORMAT_HTML).'</a>
                                <i class="hidden-label edw-icon edw-icon-Hide p-mt-0d5 catvisibility-'.$category->visible.'" aria-hidden="true"></i>
                                </div></li>
                            </ul>';
            }

            if ($hasChildren) {
                $html .= catalogue_utility::generateHTML($category->children);
                $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        // $html .= '</div>';

        return $html;
    }

    /*
     * To add menu in header bar for recently accessed cources.
     * @param $contextmenu -> $primarymenu['moremenu']
     */
    public static function get_recent_courses_menu($contextmenu) {

        $courses = \theme_remui_coursehandler::get_recent_accessed_courses(5);

        $mainarr = [];
        $mainarr['text'] = get_string('recent', 'theme_remui');
        $mainarr['srtext'] = get_string('recentcoursesmenu', 'theme_remui');
        $mainarr['key'] = 'recentcourses';
        $mainarr['url'] = "#";
        $mainarr['children'] = [];
        foreach ($courses as $key => $course) {

            $mainarr['haschildren'] = true;

            $obj = [];

            $obj['text'] = format_text($course->fullname);
            $obj['url'] = new moodle_url('/course/view.php?id=', array(
                'id' => $course->courseid
            ));
            $obj['title'] = format_text($course->fullname);
            $mainarr['children'][] = $obj;
        }

        if (isset($mainarr['haschildren']) && $mainarr['haschildren']) {
            // To add recent menu at start.
            // Code - $contextmenu['nodearray'] = array_merge(array($mainarr), $contextmenu['nodearray']).
            // To add recent menu at end.
            // Code - $contextmenu['moremenu']['nodearray'][] = $mainarr.

            // This code is to separate menu from primary menu.
            $contextmenu['edwisermenu']['nodearray'][] = $mainarr;

            // Mobile Menu addition.
            $contextmenu['mobileprimarynav'][] = $mainarr;
        }

        return $contextmenu;
    }
    public static function get_login_menu_data($primarymenu) {
        global $PAGE;

        $loginpopup = [];

        $loginpopup['forgotpasswordurl'] = (new moodle_url('/login/forgot_password.php'))->__toString();
        $loginpopup['loginurl'] = get_login_url();
        $loginpopup['logintoken'] = \core\session\manager::get_login_token();

        $authsequence = get_enabled_auth_plugins(true); // Get all auths, in sequence.

        $idps = array();
        foreach ($authsequence as $authname) {
            $authplugin = get_auth_plugin($authname);
            $idps = array_merge($idps, $authplugin->loginpage_idp_list($PAGE->url->out(false)));
        }

        if (!empty($idps)) {
            $loginpopup['authmethods'] = [];
            foreach ($idps as $idp) {
                $authmethod = [];
                $loginpopup['hasauthmethods'] = true;
                $authmethod['url'] = $idp['url']->out();
                $authmethod['name'] = $idp['name'];

                if (!empty($idp['iconurl'])) {
                    $authmethod['iconurl'] = $idp['iconurl'];
                }
                $loginpopup['authmethods'][] = $authmethod;
            }
        }

        $primarymenu['user']['unauthenticateduser']['loginpopup'] = $loginpopup;
        unset($primarymenu['user']['unauthenticateduser']['url']);

        return $primarymenu;
    }


    /**
     * This function is used to get the sections for footer section.
     *
     * @return array which contains the column values
     */

    public static function get_sections() {
        $sectionvalue = 0;
        $allsections = [];
        $noofwidgests = get_config('theme_remui', 'footercolumn');
        for ($i = 1; $i <= $noofwidgests; $i++) {
            $allsections[] = $i;

        }
        if (count($allsections) < 4) {
            $allsections = array_pad($allsections, 4, 0);
        };
        return $allsections;
    }

    /**
     * Check user is admin or manager
     * @param  object  $userobject User object
     * @return boolean             True if admin or manager
     */
    public static function check_user_admin_cap($userobject = null) {
        global $USER;
        if (!$userobject) {
            $userobject = $USER;
        }
        if (is_siteadmin()) {
            return true;
        }
        $context = \context_system::instance();
        $roles = get_user_roles($context, $userobject->id, false);
        foreach ($roles as $role) {
            if ($role->roleid == 1 && $role->shortname == 'manager') {
                return true;
            }
        }
        return false;
    }
    /**
     * Generates an array of sections and an array of activities for the given course.
     *
     * This method uses the cache to improve performance and avoid the get_fast_modinfo call
     *
     * @param stdClass $course
     * @return array Array($sections, $activities)
     */
    public static function get_focus_mode_sections(stdClass $course, $coursemoduleid = false) {
        global $CFG, $USER;
        require_once($CFG->dirroot.'/course/lib.php');

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        // For course formats using 'numsections' trim the sections list.
        $courseformatoptions = course_get_format($course)->get_format_options();
        if (isset($courseformatoptions['numsections'])) {
            $sections = array_slice($sections, 0, $courseformatoptions['numsections'] + 1, true);
        }

        $allsections = array();
        $active = '';
        $previous = '';
        $current = '';
        $next = '';

        foreach ($sections as $sectiondata) {
            $section = new stdClass;
            $section->sectionid = 'Section-'.$sectiondata->id;
            $section->section = $sectiondata->section;
            $section->name = get_section_name($course, $sectiondata->section);
            $section->hasactivites = false;
            $section->activities = [];
            $section->active = '';
            if (!array_key_exists($sectiondata->section, $modinfo->sections)) {
                continue;
            }

            foreach ($modinfo->sections[$sectiondata->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];

                // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
                if (!$cm->uservisible || $cm->is_stealth() || empty($cm->url)) {
                    continue;
                }
                $activity = new stdClass;
                $activity->id = $cm->id;
                $activity->course = $course->id;
                $activity->section = $sectiondata->section;
                $activity->name = strip_tags(format_text($cm->name));
                $activity->icon = $cm->get_icon_url();
                $activity->hidden = (!$cm->visible);
                $activity->modname = $cm->modname;
                $activity->onclick = $cm->onclick;
                $activity->active = '';
                $url = $cm->url;
                if (!$url) {
                    $activity->url = null;
                    $activity->display = false;
                } else {
                    $activity->url = $url->out();
                    $activity->display = $cm->is_visible_on_course_page() ? true : false;
                }
                if ($activity->display) {
                    if ($coursemoduleid != false) {
                        if ($active == '') {
                            $previous = $current;
                            $current = $activity->url;
                        }
                        if ($active != '' && $next == '') {
                            $next = $activity->url;
                        }
                        if ($cm->id == $coursemoduleid) {
                            $activity->active = 'active';
                            $active = $activity->name;
                            $section->active = 'show';
                        }
                    }
                    $section->hasactivites = true;
                    $section->activities[] = $activity;
                }
            }
            $allsections[] = $section;
        }
        if (count($allsections) != 0 && $active == '') {
            $allsections[0]->active = 'show';
            $allsections[count($allsections) - 1]->last = true;
        }

        // Make forceview true for previous and next link.
        if ($previous != '') {
            $previous .= '&forceview=1';
        }
        if ($next != '') {
            $next .= '&forceview=1';
        }

        return [$allsections, $active, $previous, $next];
    }

    /**
     * Get user picture from user object
     * @param  object  $userobject User object
     * @param  integer $imgsize    Size of image in pixel
     * @return String              User picture link
     */
    public static function get_user_picture($userobject = null, $imgsize = 100) {
        global $USER, $PAGE;
        if (!$userobject) {
            $userobject = $USER;
        }

        $userimg = new user_picture($userobject);
        $userimg->size = $imgsize;
        return  $userimg->get_url($PAGE);
    }
    /**
     * Add extra body classes.
     * Do some data manipulation then add your classes.
     */
    public static function get_main_bg_class() {
        global $PAGE;
        $haystack = explode(" ", $PAGE->bodyclasses);

        if (in_array('ignore-main-bg', $haystack)) {
            return;
        }

        $bodyclasses = array(
            'pagelayout-mydashboard',
            'pagelayout-mycourses',
            'pagelayout-frontpage',
            'path-calendar',
            'pagelayout-course'
        );

        foreach ($bodyclasses as $key => $needle) {
            if (in_array($needle, $haystack)) {
                return;
            }
        }

        return "main-area-bg";
    }

       /**
        * Get card content for courses
        * @param  object $wdmdata Data to create cards
        * @param  string $date    Date filter
        * @return array           Courses cards
        */
    public static function get_course_cards_content($wdmdata, $date = 'all') {
        global $CFG, $OUTPUT;

        // Resultant Array.
        $result = array();
        $result['view'] = get_user_preferences('course_view_state');

        if ((isset($wdmdata->view) && $wdmdata->view == 'grid') || (!isset($wdmdata->view) && ($result['view'] == 'grid' || !$result['view'] ))) {
            $courseperpage = self::get_rowperpage_on_coursearchive($wdmdata->courserowperpage, $wdmdata->courseperrow);
        } else {
            $courseperpage = \theme_remui\toolbox::get_setting('courseperpage');
        }

        $categorysort = $wdmdata->sort;
        $search       = $wdmdata->search;
        $category     = $wdmdata->category;
        $courses      = isset($wdmdata->courses) ? $wdmdata->courses : [];
        $mycourses    = $wdmdata->tab;
        $page         = ($mycourses) ? $wdmdata->page->mycourses : $wdmdata->page->courses;
        $startfrom    = $courseperpage * $page;
        $limitto      = $courseperpage;
        $filtermodified = isset($wdmdata->isFilterModified) ? $wdmdata->isFilterModified : true;
        $allowfull = true;
        $isfilterapplied = $wdmdata->isfilterapplied ? true : false;

        $filteredcourseids = self::get_all_filtered_courseids($wdmdata->selectedFilters);

        if ($page == -1) {
            $startfrom = 0;
            $limitto = 0;
        }

        // This condition is for coursecategory page only, that is why on frontpage it is not
        // necessary so returning limiteddata.
        if (isset($wdmdata->limiteddata)) {
            $allowfull = false;
        }

        // Pagination Context creation.
        if ($wdmdata->pagination) {
            // First paremeter true means get_courses function will return count of the result and if false, returns actual data.
            list($totalcourses, $courses)  = self::get_courses(
                2,
                $search,
                $category,
                $startfrom,
                $limitto,
                $mycourses,
                $categorysort,
                $courses,
                $filtermodified,
                $filteredcourseids,
                $isfilterapplied
            );

            $pagingbar  = new \paging_bar($totalcourses, $page, $courseperpage, 'javascript:void(0);', 'page');
            $result['totalcoursescount'] = $totalcourses;
            $result['pagination'] = $OUTPUT->render($pagingbar);
        } else {
            // Fetch the courses.
            $courses = self::get_courses(
                false,
                $search,
                $category,
                $startfrom,
                $limitto,
                $mycourses,
                $categorysort,
                $courses,
                $filtermodified,
                $filteredcourseids,
                $isfilterapplied
            );
        }

        // Courses Data.
        $coursecontext = array();
        foreach ($courses as $key => $course) {


            $coursedata = array();
            $coursedata['id'] = $course['courseid'];
            $coursedata['grader']    = $course['grader'];
            $coursedata['shortname'] = strip_tags(format_text($course['shortname']));
            $coursedata['courseurl'] = $course['courseurl'];
            $coursedata['coursename'] = strip_tags(format_text($course['coursename']));
            $coursedata['enrollusers'] = $course['enrollusers'];
            $coursedata['editcourse']  = $course['editcourse'];
            $coursedata['activity']    = $course['activity'];
            $coursedata['categoryname'] = strip_tags(format_text($course['categoryname']));
            $coursedata['ernrshortdesign'] = $course['ernrshortdesign'];
            $coursedata['lessonstitletext'] = $course['lessonstitletext'];
            $coursedata['enrolledusertitletext'] = $course['enrolledusertitletext'];
            $coursedata['skillleveltag'] = $course['skillleveltag'];
            if ($course['visible']) {
                $coursedata['visible'] = $course['visible'];
            }

            // This condition to handle the string url or moodle_url object problem.
            if (is_object($course['courseimage'])) {
                $coursedata['courseimage'] = $course['courseimage']->__toString();
            } else {
                $coursedata['courseimage'] = $course['courseimage'];
            }
            $coursedata['coursesummary'] = $course['coursesummary'];


            // Context creation for all courses.
            if (isset($course['usercanmanage']) && $allowfull) {
                $coursedata["usercanmanage"] = $course['usercanmanage'];
            }

            if (isset($course['enrollmenticons']) && $allowfull) {
                $coursedata["enrollmenticons"] = $course['enrollmenticons'];
            }
            if(isset($course["enrollmenticonsremainig"])) {
                $coursedata["enrollmenticonsremainig"] = $course["enrollmenticonsremainig"];
            }

            if(isset($course["enrolleduserscount"])){
                $coursedata["enrolleduserscount"] = $course["enrolleduserscount"];
            }

            $coursedata["showselecteddatesetting"] = $course["showselecteddatesetting"];

            if (isset($course['instructors']) && $allowfull) {
                $instructors = array();
                foreach ($course['instructors'] as $key2 => $instructor) {
                    $instructordetail['name'] = $instructor['name'];
                    $instructordetail['url'] = $instructor['url'];
                    $instructordetail['picture'] = $instructor['picture']->__toString();
                    $instructors[] = $instructordetail;
                }
                $coursedata['instructors'] = $instructors;
            }

            $coursedata['instructorcount'] = $course['instructorcount'];
            $coursedata['lessoncount'] = $course['lessoncount'];
            // $pagelayout = get_config('theme_remui', 'categorypagelayout');

            $coursedata['animation'] = \theme_remui\toolbox::get_setting('courseanimation');
            $coursecontext[] = $coursedata;
        }
        $result['courses'] = $coursecontext;


        return $result;
    }

    /**
     * Returns the number of rows to display on the course archive page based on the cards per page value.
     *
     * @param int $cardsperpagevalue The number of cards to display per page.
     * @return int The number of rows to display on the course archive page.
     */
    public static function get_rowperpage_on_coursearchive($courserowperpage, $courseperrow = 4) {
        global $OUTPUT;

        if ($courseperrow == 1) {
            return 6;
        }

        if (!$courserowperpage) {
            $courserowperpage = \theme_remui\toolbox::get_setting('courseperpage');
            $courserowperpage = $courserowperpage / 3;
        }

        return $courserowperpage * $courseperrow;
    }

        /**
         * Return user's courses or all the courses
         *
         * Usually called to get usr's courese, or it could also be called to get all course.
         * This function will also be called whern search course is used.
         *
         * @param  bool   $totalcount     If true then returns total course count
         * @param  string $search         course name to be search
         * @param  int    $category       ids to be search of courses.
         * @param  int    $limitfrom      course to be returned from these number onwards, like from course 5 .
         * @param  int    $limitto        till this number course to be returned,
         *                                like from course 10, then 5 course will be returned from 5 to 10.
         * @param  array  $mycourses      Courses to return user's course which he/she enrolled into.
         * @param  bool   $categorysort   if true the categories are sorted
         * @param  array  $courses        pass courses if would like to load more data for those courses
         * @param  bool   $filtermodified If true then cache will be cleared and regenerated when filter is modified
         * @return array                  Course array
         */
    public static function get_courses(
        $totalcount = false,
        $search = null,
        $category = null,
        $limitfrom = 0,
        $limitto = 0,
        $mycourses = null,
        $categorysort = null,
        $courses = [],
        $filtermodified = true,
        $filteredcourseids = [],
        $isfilterapplied = false
    ) {
        $coursehandler = new \theme_remui_coursehandler();
        return $coursehandler->get_courses(
            $totalcount,
            $search,
            $category,
            $limitfrom,
            $limitto,
            $mycourses,
            $categorysort,
            $courses,
            $filtermodified,
            $filteredcourseids,
            $isfilterapplied
        );

    }
    /**
     * Return HTML for site announcement.
     *
     * @return string Site announcement HTML
     */
    public static function render_site_announcement() {

        $announce = '';
        $classes = '';
        $html = '';

        $type = \theme_remui\toolbox::get_setting('announcementtype');
        $message = format_text(\theme_remui\toolbox::get_setting('announcementtext'),FORMAT_HTML,array("noclean" => true));

        if (\theme_remui\toolbox::get_setting('enabledismissannouncement')) {
            $html .= '<button id="dismiss_announcement" type="button" class="close" data-dismiss="alert" aria-label="Close">';
            $html .= '<span aria-hidden="true" class="edw-icon edw-icon-Cancel large"></span>';
            $html .= '</button>';
            $classes = 'alert-dismissible';
        }

        $classes .= ' site-announcement mb-0';
        $announce .= "<div class='alert alert-{$type} {$classes} show-more' role='alert'>";
        $announce .= "<span class='ellipsis ellipsis-1'>{$message}</span>";
        $announce .= $html;
        $announce .= "</div>";

        return $announce;
    }
    /**
     * Function to get the remote data from url
     *
     * @param string $url
     * @return array
     */
    public static function url_get_contents ($url) {
        global $CFG;
        $urlgetcontentsdata = array();

        if (class_exists('curl')) {
            $curl = new \curl();
            $curl->setopt(array(
                'CURLOPT_SSL_VERIFYPEER' => false,
                'CURLOPT_FRESH_CONNECT' => true,
                'CURLOPT_RETURNTRANSFER' => 1,
                'CURLOPT_TIMEOUT' => 3,
                'CURLOPT_USERAGENT' => $_SERVER['HTTP_USER_AGENT'] . ' - ' . $CFG->wwwroot,
            ));
            $urlgetcontentsdata = $curl->get($url);

            if ($curl->get_errno() !== 0) {
                $urlgetcontentsdata = [];
            }
        } else if (function_exists('curl_exec')) {
            $conn = \curl_init($url);
            curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn, CURLOPT_TIMEOUT, 3);
            curl_setopt($curl, CURLOPT_USERAGENT, \core_useragent::get_user_agent_string());
            curl_setopt($curl, CURLOPT_REFERER, $CFG->wwwroot);
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($conn, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            $urlgetcontentsdata = (curl_exec($conn));
            if (curl_errno($conn)) {
                $errormsg = curl_error($conn);
                $urlgetcontentsdata = array();
            }
            curl_close($conn);
        } else if (function_exists('file_get_contents')) {
            $urlgetcontentsdata = file_get_contents($url);
        } else if (function_exists('fopen') && function_exists('stream_get_contents')) {
            $handle = fopen($url, "r");
            $urlgetcontentsdata = stream_get_contents($handle);
        } else {
            $urlgetcontentsdata = array();
        }
        return $urlgetcontentsdata;
    }
    /**
     * Throw error with string and error code
     * @param string $error     Eigther error string id or direct string
     * @param int    $code      Numeric error code
     * @param bool   $getstring If true then $error will be treated as string id
     */
    public static function throw_error($error, $code = '', $getstring = true) {
        if ($getstring) {
            $error = get_string($error, 'theme_remui');
        }
        throw new Exception(json_encode(['error' => true, 'msg' => $error . " : " . $code]), $code);
    }

    public static function get_inproduct_notification() {
        global $OUTPUT;
        // Init product notification configuration
        $notification = get_user_preferences('edwiser_inproduct_notification');

        if ($notification == null || $notification == "false" || $notification == false) {
            return false;
        }

        $notification = json_decode($notification);

        return [
            "msg" => $notification->msg,
            "imgclass" => $notification->img,
            "edwiserlogo" => $OUTPUT->image_url('edwiser-logo', 'theme_remui')->__toString(),
            "mainimg" => $OUTPUT->image_url($notification->img, 'theme_remui')->__toString()
        ];
    }

        /**
         * Show license or update notice
         *
         * @return HTML for license notice.
         */
    public static function show_license_notice() {
        // Get license data from license controller.
        $lcontroller = new \theme_remui\controller\LicenseController();
        $getlidatafromdb = $lcontroller->get_data_from_db();
        if (isloggedin() && !isguestuser()) {
            $content = '';

            $classes = ['alert', 'text-center', 'license-notice', ' alert-dismissible', 'site-announcement' , 'mb-0'];
            if ('available' != $getlidatafromdb) {
                $classes[] = 'alert-danger';
                if (is_siteadmin()) {
                    $content .= '<strong>'.get_string('licensenotactiveadmin', 'theme_remui').'</strong>';
                } else {
                    $content .= get_string('licensenotactive', 'theme_remui');
                }
            } else if ('available' == $getlidatafromdb) {
                $licensekeyactivate = \theme_remui\toolbox::get_setting(EDD_LICENSE_ACTION);

                if (isset($licensekeyactivate) && !empty($licensekeyactivate)) {
                    $classes[] = 'alert-success';
                    $content .= get_string('licensekeyactivated', 'theme_remui');
                } else {
                    // Show update notice if license is active and update is available.
                    $available  = \theme_remui\controller\RemUIController::check_remui_update();
                    if (is_siteadmin() && $available == 'available') {
                        $classes[] = 'update-nag bg-info moodle-has-zindex';
                        $url = new moodle_url(
                            '/admin/settings.php',
                            array(
                                'section' => 'themesettingremui',
                                'activetab' => 'informationcenter'
                            )
                        );
                        $content .= get_string('newupdatemessage', 'theme_remui', $url->out());
                    }
                }
            }
            if ($content != '') {
                $content .= '<button type="button" id="dismiss_announcement" class="close" data-dismiss="alert" aria-hidden="true"><span class="edw-icon edw-icon-Cancel  large"></span></button>';
                return html_writer::tag('div', $content, array('class' => implode(' ', $classes)));
            }
        }
        return '';
    }
    public static function remove_announcement_preferences() {
        global $DB;
        // Delete from DB.
        $DB->delete_records('user_preferences', array('name' => 'remui_dismised_announcement'));
    }

    /**
     * Get a list of course IDs that match the specified filters.
     *
     * This function takes an array of filters (price, rating, skill level, language) and returns an array of course IDs that match those filters.
     *
     * @param array $selectedfilters An associative array of filters, where the keys are the filter types and the values are the filter values.
     * @return array An array of course IDs that match the specified filters.
     */
    public static function get_all_filtered_courseids($selectedfilters) {

        if (!$selectedfilters) {
            return [];
        }

        $pricecourseids = [];
        $ratingcourseids = [];
        $skilllevelcourseids = [];
        $languagecourseids = [];

        foreach ($selectedfilters as $filtertype => $filters) {
            switch ($filtertype) {
                case 'price':
                    $pricecourseids = self::get_price_filtered_courseids($filters);
                    break;
                case 'rating':
                    $ratingcourseids = self::get_rating_filtered_courseids($filters);
                    break;
                case 'skilllevel':
                    $skillvalues = array_map(function($skill) {
                        return $skill->value;
                    }, $filters);
                    $skilllevelcourseids = self::get_skilllevel_filtered_courseids($skillvalues);
                    break;
                case 'language':
                    $languagecodes = array_map(function($lang) {
                        return $lang->value;
                    }, $filters);
                    $languagecourseids = self::get_language_filtered_courseids($languagecodes);
                    break;
            }
        }

        $arrays = [
            $pricecourseids,
            $ratingcourseids,
            $skilllevelcourseids,
            $languagecourseids,
        ];

        // Filter out empty arrays.
        $nonemptyarrays = array_filter($arrays, function($arr) {
            return !empty($arr);
        });

        // If there are no non-empty arrays, result will be an empty array.
        if (empty($nonemptyarrays)) {
            $result = [];
        } else {
            // Use array_intersect with splat operator to intersect all non-empty arrays.
            $result = array_intersect(...$nonemptyarrays);
        }

        return $result;
    }

    /**
     * Get the list of course IDs that match the specified price filters.
     *
     * This function takes an array of price filters ('free' or 'paid') and returns an array of course IDs that match those filters.
     *
     * @param array $filters An array of price filters ('free' or 'paid').
     * @return array An array of course IDs that match the specified price filters.
     */
    public static function get_price_filtered_courseids($filters) {
        $courseids = [];
        foreach ($filters as $key => $filter) {
            switch ($filter->value) {
                case 'free':
                    $courseids = array_unique(array_merge($courseids, self::list_of_coursesids_by_price()));
                    break;
                case 'paid':
                    $courseids = array_unique(array_merge($courseids, self::list_of_coursesids_by_price(1)));
                    break;
            }
        }

        return $courseids;
    }

    /**
     * Get the list of course IDs that match the specified rating filters.
     *
     * This function takes an array of rating filters and returns an array of course IDs that match those filters.
     *
     * @param array $filters An array of rating filters.
     * @return array An array of course IDs that match the specified rating filters.
     */
    public static function get_rating_filtered_courseids($filters) {
        $courseids = [];
        foreach ($filters as $key => $filter) {
            $courseids = array_unique(array_merge($courseids, self::get_courseids_by_ratings($filter->value)));
        }
        return $courseids;
    }

    /**
     * Get the list of course IDs that match the specified skill level filters.
     *
     * This function takes an array of skill level filters and returns an array of course IDs that match those filters.
     * It does this by querying the {customfield_field} and {customfield_data} tables to find courses that have a custom field
     * with the shortname 'edwskilllevel' and a value that matches the provided filters.
     *
     * @param array $skillvalues An array of skill level filters.
     * @return array An array of course IDs that match the specified skill level filters.
     */
    public static function get_skilllevel_filtered_courseids($skillvalues) {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal($skillvalues, SQL_PARAMS_NAMED, 'param', true);

        $sql = "SELECT DISTINCT cd.instanceid AS courseid
                FROM {customfield_field} cf
                JOIN {customfield_data} cd ON cf.id = cd.fieldid
                WHERE cf.shortname = :shortname AND " . $DB->sql_cast_char2int('cd.intvalue') . " $insql";

        $params = array_merge(['shortname' => 'edwskilllevel'], $inparams);

        $records = $DB->get_records_sql($sql, $params);

        $courseids = array_keys($records);

        if (in_array(0, $skillvalues, true) || in_array('0', $skillvalues, true)) {
            $noskilllevelcourseids = self::get_noskill_level_couresid();
            $courseids = array_merge($courseids, $noskilllevelcourseids);
        }

        return $courseids;
    }

    /**
     * Get the list of course IDs that match the specified language filters.
     *
     * This function takes an array of language filters and returns an array of course IDs that match those filters.
     * It does this by querying the {course} table and checking the 'lang' column for a match with the provided filters.
     *
     * @param array $filters An array of language filters.
     * @return array An array of course IDs that match the specified language filters.
     */
    public static function get_language_filtered_courseids($languagecodes) {
        global $DB;

        list($insql, $params) = $DB->get_in_or_equal($languagecodes, SQL_PARAMS_NAMED);

        $sql = "SELECT id
                FROM {course}
                WHERE lang $insql";

        $records = $DB->get_records_sql($sql, $params);

        $nolangsetcourseids = [];
        if(in_array('en', $languagecodes)) {
            $nolangsetcourseids = self::get_nolangset_coursesids();
        }

        $courseids = array_keys($records);

        return array_merge($courseids, $nolangsetcourseids);
    }

    /**
     * Get the list of course IDs sorted according to their average rating.
     *
     * This function retrieves the list of course IDs sorted in descending order by their average rating.
     * It does this by querying the {course} and {block_edwiserratingreview} tables, calculating the average
     * rating for each course, and then returning the course IDs sorted by the average rating.
     *
     * @return array An array of course IDs sorted by their average rating in descending order.
     */
    public static function get_all_courseids_sorted_according_rating() {
        global $DB;

        $sql = "SELECT c.id, COALESCE(AVG(" . $DB->sql_cast_char2real('r.star_ratings') . "), 0) as avg_rating
                FROM {course} c
                LEFT JOIN {block_edwiserratingreview} r ON c.id = r.for_id AND r.approved = 1
                GROUP BY c.id
                ORDER BY avg_rating DESC";

        $results = $DB->get_records_sql($sql);

        return array_keys($results);
    }


    /**
     * Get the list of course IDs that have a custom price set.
     *
     * This function retrieves the list of course IDs that have a custom price set in the site configuration.
     * It does this by querying the {config_plugins} table for plugin configuration entries that start with
     * 'custompricetext' and have a non-empty value.
     *
     * @return array An array of course IDs that have a custom price set.
     */
    public static function custom_price_set_coursesids() {
        global $DB;

        $sql = "SELECT name, value
        FROM {config_plugins}
        WHERE name LIKE :custompricetext
        AND " . $DB->sql_isnotempty('config_plugins', 'value', false, true);

        $params = ['custompricetext' => 'custompricetext%'];

        $cpsetcourseids = $DB->get_fieldset_sql($sql, $params);

        $cpsetcourseids = array_map(function($item) {
            return (int) str_replace('custompricetext', '', $item);
        }, $cpsetcourseids);

        return $cpsetcourseids;
    }

    /**
     * Get the list of course IDs that are either free or paid.
     *
     * This function retrieves the list of course IDs based on whether the course is free or paid.
     * It does this by querying the {enrol} table and checking the 'cost' column to determine if the course is paid or not.
     *
     * @param int $ispaid If set to 1, the function will return the list of paid course IDs.
     * If set to 0, the function will return the list of free course IDs.
     * @return array An array of course IDs that match the specified paid/free criteria.
     */
    public static function list_of_coursesids_by_price($ispaid = 0) {
        global $DB;

        $sql = "SELECT courseid
            FROM (
                SELECT
                    courseid,
                    MAX(CASE WHEN COALESCE(CAST({$DB->sql_cast_char2real('cost')} AS DECIMAL(10,2)), 0) > 0 THEN 1 ELSE 0 END) AS is_paid
                FROM {enrol}
                GROUP BY courseid
            ) course_status
            WHERE is_paid = :ispaid";

        $params = ['ispaid' => $ispaid];
        $coursesids = $DB->get_fieldset_sql($sql, $params);

        // If the enrolment page layout is not set to Edwiser Layout than return the coursesid according to moodle payment status.
        if (!(\theme_remui\toolbox::get_setting('enrolment_page_layout') == 1)) {
            return $coursesids;
        }

        $cpsetcourseids = self::custom_price_set_coursesids();

        if ($ispaid) {
            $paidcoursesids = array_unique(array_merge($coursesids, $cpsetcourseids));
            return $paidcoursesids;
        } else {
            $freecoursesids = array_diff($coursesids, $cpsetcourseids);
            $freecoursesids = array_values($freecoursesids);
            return $freecoursesids;
        }
    }

    /**
     * Generate pricing filter data for courses.
     *
     * This function checks the enrolment status of courses and calculates the count of free and paid courses.
     * It returns an array containing the pricing filter data, which includes the count of free and paid courses.
     *
     * @return array|null An array containing pricing filter data, or null if no data is available.
     */
    public static function generate_pricing_filter_data($courseids) {

        if (empty($courseids)) {
            return null;
        }

        $freecoursesids  = self::list_of_coursesids_by_price();
        $paidcoursesids = self::list_of_coursesids_by_price(1);

        $freecoursesids = array_intersect($freecoursesids, $courseids);
        $paidcoursesids = array_intersect($paidcoursesids, $courseids);

        $pricingfilter = null;

        if ($freecoursesids && $paidcoursesids) {
            $pricingfilter = [
                'filterlabel' => get_string('price', 'theme_remui'),
                'filtertype' => 'price',
                'filteroptions' => [
                    [
                        'name' => 'free',
                        'value' => 'free',
                        'text' => get_string('free', 'theme_remui'),
                        'count' => count($freecoursesids),
                    ],
                    [
                        'name' => 'paid',
                        'value' => 'paid',
                        'text' => get_string('paid', 'theme_remui'),
                        'count' => count($paidcoursesids),
                    ],
                ],
            ];
        }

        return $pricingfilter;
    }

    /**
     * Get a list of course IDs based on a minimum rating threshold.
     *
     * This function retrieves a list of course IDs where the average rating for the course is greater than or equal to the specified threshold.
     * It uses the `block_edwiserratingreview` table to fetch the approved ratings for each course, and then groups the results by course ID to calculate the average rating.
     *
     * @param int $rating The minimum rating threshold to filter courses by.
     * @return array An array of course IDs that meet the rating threshold.
     */
    public static function get_courseids_by_ratings($rating) {
        global $DB;
        $sql = "SELECT for_id
                FROM {block_edwiserratingreview}
                WHERE approved = 1
                GROUP BY for_id
                HAVING AVG(" . $DB->sql_cast_char2real('star_ratings') . ") >= :threshold";

        $courseids = $DB->get_fieldset_sql($sql, ['threshold' => $rating]);

        return $courseids;
    }

    /**
     * Checks if any course has a rating.
     *
     * This function checks if the Edwiser Rating Review plugin is available and if there are any approved ratings for courses in the system.
     *
     * @return bool True if there are any approved course ratings, false otherwise.
     */
    public static function has_any_course_rating() {
        global $DB;

        if (!is_plugin_available("block_edwiserratingreview")) {
            return false;
        }

        $sql = "SELECT 'x' AS result
                FROM {block_edwiserratingreview} r
                WHERE r.approved = 1";

        return $DB->record_exists_sql($sql, null);
    }

    /**
     * Generate rating filter data for courses.
     *
     * This function checks if the Edwiser Rating Review plugin is available and generates
     * rating filter data based on course ratings. It calculates the count of courses with
     * ratings of 4 and above, and 3 and above.
     *
     * @return array|null An array containing rating filter data, or null if no data is available.
     */
    public static function generate_rating_filter_data($courseids) {
        global $DB;

        if (empty($courseids) || !self::has_any_course_rating()) {
            return null;
        }

        $rating4count = 0;
        $rating3count = 0;

        $ratings = [
            ['name' => 'rating4', 'text' => get_string('rating4', 'theme_remui'), 'threshold' => 4],
            ['name' => 'rating3', 'text' => get_string('rating3', 'theme_remui'), 'threshold' => 3],
        ];

        $values = [];

        foreach ($ratings as $rating) {
            $courseidsbyrating = self::get_courseids_by_ratings($rating['threshold']);
            $courseidsbyrating = array_intersect($courseidsbyrating, $courseids);

            $count = count($courseidsbyrating);

            if ($count) {
                $values[] = [
                    'name' => $rating['name'],
                    'value' => $rating['threshold'],
                    'text' => $rating['text'],
                    'count' => $count,
                ];

                if ($rating['name'] == 'rating4') {
                    $rating4count = $count;
                }
                if ($rating['name'] == 'rating3') {
                    $rating3count = $count;
                }
            }
        }

        if (($rating4count == $rating3count || $rating4count == 0) && ($rating3count == count($courseids))) {
            return null;
        }

        return !empty($values) ?
            [
                'filterlabel' => get_string('ratings', 'theme_remui'),
                'filtertype' => 'rating',
                'filteroptions' => $values,
            ]
            : null;
    }

    public static function get_noskill_level_couresid() {
        global $DB;

        $where = "cf.shortname = :shortname";
        $params = ['shortname' => 'edwskilllevel', 'siteid' => SITEID];

        $noskilllevelcourseidssql = " SELECT
            c.id AS courseid
        FROM
            {course} c
        WHERE
            c.id <> :siteid and c.id NOT IN (
                SELECT
                    cd.instanceid
                FROM
                    {customfield_data} cd
                INNER JOIN
                    {customfield_field} cf
                ON
                    cd.fieldid = cf.id
                WHERE
                    $where
            )
        ";

        $noskilllevelcourseids = $DB->get_records_sql($noskilllevelcourseidssql, $params);
        return array_keys($noskilllevelcourseids);
    }

    /**
     * Generates the data for the level filter options in the courses filters menu.
     *
     * The function retrieves the count of courses for each level (Beginner, Intermediate, Advanced)
     * based on the 'Skill Level' custom field, and returns an array containing the level name, level
     * value, and course count for each level. If there are less than two levels, the function returns
     * null.
     *
     * @return array|null The level filter data, or null if there is only one level.
     */
    public static function generate_level_filter_data($courseids) {
        global $DB;

        if (empty($courseids)) {
            return null;
        }

        $where = "cf.shortname = :shortname";
        $params = ['shortname' => 'edwskilllevel'];

        if (!empty($courseids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $where .= " AND cd.instanceid $insql";
            $params = $params + $inparams;
        }

        $sql = "SELECT cd.intvalue, COUNT(cd.intvalue) AS count
                FROM {customfield_field} cf
                INNER JOIN {customfield_data} cd
                ON cf.id = cd.fieldid
                WHERE $where
                GROUP BY cd.intvalue
                ORDER BY cd.intvalue";

        $result = $DB->get_records_sql($sql, $params);

        $noskilllevelcourseids = self::get_noskill_level_couresid();

        if (!empty($courseids)) {
            $noskilllevelcourseids = array_intersect($noskilllevelcourseids, $courseids);
        }

        $noskilllevelcount = count($noskilllevelcourseids);

        $key = array_search(0, array_column($result, 'intvalue'));

        if ($key !== false) {
            $result[$key]->count += $noskilllevelcount;
        } else if ($noskilllevelcount != 0) {
            $result[] = (object)[
                'intvalue' => 0,
                'count' => $noskilllevelcount,
            ];
        }

        $levelfilter = null;

        if (count($result) >= 2) {
            $levelfilter = [
                'filterlabel' => get_string('level', 'theme_remui'),
                'filtertype' => 'skilllevel',
                'filteroptions' => [],
            ];

            foreach ($result as $key => $leveldata) {
                $levelname = get_string('skill'.$leveldata->intvalue, 'theme_remui');

                $levelfilter['filteroptions'][] = [
                    'name' => "skill" . $leveldata->intvalue,
                    'value' => $leveldata->intvalue,
                    'text' => $levelname,
                    'count' => $leveldata->count,
                ];
            }
        }

        return $levelfilter;
    }

    public static function get_nolangset_coursesids() {
        global $DB;

        $sql = "SELECT id
                FROM {course}
                WHERE id <> :siteid AND (lang IS NULL OR lang = :emptylang)";

        $params = [
            'siteid' => SITEID,
            'emptylang' => ''
        ];

        $emptylangcourses = $DB->get_records_sql($sql, $params);
        return array_keys($emptylangcourses);
    }

    /**
     * Generates the data for the language filter options in the courses filters menu.
     *
     * The function retrieves the count of courses for each installed language, and returns an array
     * containing the language name, language code, and course count for each language. If there are
     * less than two languages, the function returns null.
     *
     * @return array|null The language filter data, or null if there is only one language.
     */
    public static function generate_language_filter_data($courseids) {
        global $DB;

        if (empty($courseids)) {
            return null;
        }

        $where = "id <> :siteid AND " . $DB->sql_isnotempty('course', 'lang', false, true);
        $params = ['siteid' => SITEID];

        if (!empty($courseids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $where .= " AND id $insql";
            $params = array_merge($params, $inparams);
        }

        $sql = "SELECT lang, COUNT(*) AS count
                FROM {course}
                WHERE $where
                GROUP BY lang";

        $result = $DB->get_records_sql($sql, $params);

        $installedlanguages = \get_string_manager()->get_list_of_translations();

        $languagefilter = null;

        $nolangsetcourseids = self::get_nolangset_coursesids();
        if (!empty($courseids)) {
            $nolangsetcourseids = array_intersect($nolangsetcourseids, $courseids);
        }

        if (!isset($result['en'])) {
            if (count($nolangsetcourseids) > 0) {
                $result['en'] = new stdClass();
                $result['en']->lang = 'en';
                $result['en']->count = count($nolangsetcourseids);
            }
        } else if (count($nolangsetcourseids)) {
            $result['en']->count += count($nolangsetcourseids);
        }

        if (count($result) >= 2) {
            $languagefilter = [
                'filterlabel' => get_string('language', 'theme_remui'),
                'filtertype' => 'language',
                'filteroptions' => [],
            ];

            $uninstalledlanguages = 0;
            foreach ($result as $langcode => $langdata) {
                if ( !isset($installedlanguages[$langcode]) ) {
                    $uninstalledlanguages++;
                    continue;
                }
                $languagename = isset($installedlanguages[$langcode]) ? $installedlanguages[$langcode] : $langcode;

                $languagefilter['filteroptions'][] = [
                    'name' => "lang".$langcode,
                    'value' => $langcode,
                    'text' => $languagename,
                    'count' => $langdata->count,
                ];
            }

            if ((count($result) - $uninstalledlanguages) < 2) {
                $languagefilter = null;
            }
        }

        return $languagefilter;
    }

    /**
     * Retrieves an array of course IDs that are visible to the current user for the specified course category.
     *
     * This function checks the user's permissions to view hidden courses and returns an array of course IDs
     * that are either visible or the user has permission to view.
     *
     * @param int|string $categoryid The ID of the course category, or 'all' to get courses from all categories.
     * @return int[] An array of course IDs that are visible to the current user.
     */
    public static function get_visible_courseids($categoryid) {
        global $DB, $USER;
        $coursehandler = new \theme_remui_coursehandler();
        $categoryids = $coursehandler->get_allowed_categories($categoryid);

        $systemcontext = context_system::instance();
        $canviewhiddencourses = has_capability('moodle/course:viewhiddencourses', $systemcontext);

        $params = array('canviewhidden' => $canviewhiddencourses);

        list($insql, $inparams) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED);
        $params = array_merge($params, $inparams);

        $sql = "SELECT id
                FROM {course}
                WHERE category $insql
                  AND (visible = 1 OR :canviewhidden = 1)";

        $courseids = $DB->get_fieldset_sql($sql, $params);

        return $courseids;
    }

    /**
     * Generates the data for filters and sorting options for courses.
     *
     * This function retrieves filter data for pricing, ratings, levels, and languages,
     * as well as sorting options for courses. It also includes the number of courses per page.
     *
     * @return array An associative array containing:
     *               - 'coursesfilters': Array of filter options (pricing, ratings, levels, languages)
     *               - 'coursesortings': Array of sorting options (date, alphabetical, ratings)
     *               - 'coursesperpagelist': Array of course per page options
     */
    public static function generate_filters_and_sorting_data($categoryid) {
        global $USER;

        $coursehandler = new \theme_remui_coursehandler();

        // Initialize the courses filters array.
        $coursesfilters = [];

        if (!$categoryid || $categoryid < 0) {
            $categoryid = 'all';
        }

        $courseids = self::get_visible_courseids($categoryid);

        // Generate filter data for different categories.
        $pricingfilters = self::generate_pricing_filter_data($courseids);
        $ratingsfilters = self::generate_rating_filter_data($courseids);
        $levelfilters = self::generate_level_filter_data($courseids);
        $languagefilters = self::generate_language_filter_data($courseids);

        // Add filters to the coursesfilters array if they exist.
        if ($pricingfilters) {
            $coursesfilters[] = $pricingfilters;
        }
        if ($ratingsfilters) {
            $coursesfilters[] = $ratingsfilters;
        }
        if ($levelfilters) {
            $coursesfilters[] = $levelfilters;
        }
        if ($languagefilters) {
            $coursesfilters[] = $languagefilters;
        }

        // Define course sorting options.
        $coursesortings = [
            [
                'sortinglabel' => get_string('date', 'theme_remui'),
                'sortingoptions' => [
                    [
                        'value' => 'newest',
                        'text' => get_string('newest', 'theme_remui'),
                    ],
                    [
                        'value' => 'oldest',
                        'text' => get_string('oldest', 'theme_remui'),
                    ],
                ],
            ],
            [
                'sortinglabel' => get_string('alphabetical', 'theme_remui'),
                'sortingoptions' => [
                    [
                        'value' => 'ASC',
                        'text' => get_string('sortascending', 'theme_remui'),
                    ],
                    [
                        'value' => 'DESC',
                        'text' => get_string('sortdescending', 'theme_remui'),
                    ],
                ],
            ],
        ];

        // Add ratings sorting option if ratings filter exists.
        if (self::has_any_course_rating()) {
            $coursesortings[] = [
                'sortinglabel' => get_string('ratings', 'theme_remui'),
                'sortingoptions' => [
                    [
                        'value' => 'highrating',
                        'text' => get_string('highrating', 'theme_remui'),
                    ],
                    [
                        'value' => 'lowrating',
                        'text' => get_string('lowrating', 'theme_remui'),
                    ],
                ],
            ];
        }

        // Get the number of courses per page from settings.
        $courseperpage = \theme_remui\toolbox::get_setting('courseperpage');

        // Define courses per page options.
        $coursesperpagelist = [
            'limitlabel' => get_string('row' . ( $courseperpage / 3 ), 'theme_remui'),
            'limitdefaultvalue' => $courseperpage,
            'limitlist' => [
                ['text' => get_string('row2', 'theme_remui'),  'value' => 2, 'isactive' => $courseperpage == 6],
                ['text' => get_string('row3', 'theme_remui'), 'value' => 3, 'isactive' => $courseperpage == 9],
                ['text' => get_string('row4', 'theme_remui'), 'value' => 4, 'isactive' => $courseperpage == 12],
                ['text' => get_string('row5', 'theme_remui'), 'value' => 5, 'isactive' => $courseperpage == 16],
                ['text' => get_string('row6', 'theme_remui'), 'value' => 6, 'isactive' => $courseperpage == 20],
            ],
        ];

        $coursesfilterscontext = [
            'isfilterinfoavailable' => empty($coursesfilters),
            'coursesfilters' => $coursesfilters,
        ];

        // If there are no any filter and user is not admin, manager. than filter will be hidden.
        if (
            (!isloggedin() || !(self::check_user_admin_cap()))
            && empty($coursesfilters)
        ) {
            $coursesfilterscontext = null;
        }

        return [
            'coursesfilterscontext' => $coursesfilterscontext,
            'coursesortingcontext' => [
                'coursesortings' => $coursesortings,
            ],
            'coursesperpagelistcontext' => [
                'coursesperpagelist' => $coursesperpagelist,
            ],
        ];
    }

    public static function get_skilllevel_by_courseid($courseid) {
        global $DB;
        $select = "cf.shortname = :shortname AND cd.instanceid = :courseid";
        $params = [
            'shortname' => 'edwskilllevel',
            'courseid' => $courseid,
        ];

        $sql = "SELECT cd.intvalue
                FROM {customfield_field} cf
                JOIN {customfield_data} cd ON cf.id = cd.fieldid
                WHERE $select";

        $result = $DB->get_field_sql($sql, $params);

        if ($result !== false) {
            $skilllevel = $result;
        } else {
            $skilllevel = false;
        }

        return $skilllevel;
    }

    /**
     * Retrieves user information feedback questions from a given URL.
     *
     * This function fetches the content from the provided URL, replaces a placeholder
     * with the site's URL, and then decodes the JSON content. If the URL is empty or
     * the content cannot be fetched or decoded, the function returns null.
     *
     * @param string $url The URL to fetch the user information feedback questions from.
     * @return array|null The decoded JSON content, or null if the fetch or decoding fails.
     */
    public static function get_content_from_json($url) {
        global $CFG;

        if (!$url) {
            return null;
        }

        // Use a timeout to prevent hanging on slow connections
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $jsoncontent = @file_get_contents($url, false, $context);

        if ($jsoncontent === false) {
            debugging('Unable to fetch whatsnew data', DEBUG_DEVELOPER);
            return null;
        }

        $jsoncontent = str_replace('{{>siteurl}}', $CFG->wwwroot, $jsoncontent);

        $jsoncontent = json_decode($jsoncontent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            debugging('Invalid JSON in whatsnew data', DEBUG_DEVELOPER);
            return null;
        }

        return $jsoncontent;
    }

    public static function check_internet_connection() {
        $hosts = ['www.google.com', 'www.cloudflare.com'];
        $ports = [80, 443];
        $timeout = 5;

        foreach ($hosts as $host) {
            foreach ($ports as $port) {
                $connected = @fsockopen($host, $port, $errno, $errstr, $timeout);
                if ($connected) {
                    fclose($connected);
                    return true;
                }
            }
        }
        return false;
    }
}
