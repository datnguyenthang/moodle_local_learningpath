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
 * This plugin serves as a database and plan for all learning activities in the organization,
 * where such activities are organized for a more structured learning program.
 * @package    local_learningpath
 * @copyright  3i Logic<lms@3ilogic.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @author     Azmat Ullah <azmat@3ilogic.com>
 */
namespace local_learningpath\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use context;
use context_system;
use core_form\dynamic_form;
use local_wunderbyte_table\wunderbyte_table;
use moodle_url;
use stdClass;
use block_learningpath\learningpath;
/**
 * Defines the learning path form.
 */
class cohort_report extends dynamic_form {

    /**
     * {@inheritdoc}
     * @see moodleform::definition()
     */
    public function definition() {
        global $OUTPUT, $DB;
    
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $ajaxformdata = $this->_ajaxformdata;
        $data = $ajaxformdata ?? $customdata;
    
        if (!empty($ajaxformdata['id'])) {
            $mform->addElement('hidden', 'id', $ajaxformdata['id']);
        }

        $mform->disable_form_change_checker();
        
        $arr = explode('-', $ajaxformdata['id']);
        // Cohort ID should be provided in custom data
        $lpt_id = $arr[0];
        $cohort_id = $arr[1];
        
        if (!$cohort_id) {
            $mform->addElement('html', '<p style="color:red;">Cohort ID is missing!</p>');
            return;
        }

        // Fetch cohort users
        $cohortusers = $DB->get_records_sql("
            SELECT u.id, u.firstname, u.lastname, u.email 
            FROM {cohort_members} cm
            JOIN {user} u ON cm.userid = u.id
            WHERE cm.cohortid = ?", [$cohort_id]);

        if (!$cohortusers) {
            $mform->addElement('html', '<p>'.get_string('nousercohort', 'local_learningpath').'</p>');
            return;
        }

        $tablehtml = '<table class="generaltable">';
        $tablehtml .= '<tr>
                        <th>'.get_string('name', 'local_learningpath').'</th>
                        <th>'.get_string('email').'</th>
                        <th>'.get_string('progress').'</th>
                    </tr>';
    
        foreach ($cohortusers as $user) {
            //$user = $DB->get_record('user', ['id' => $user->id]);
            $progress = self::get_completion_progress($user->id, $lpt_id);
            $tablehtml .= '
            <tr>
                <td>'.fullname($user).'</td>
                <td>'.$user->email.'</td>
                <td>'.$progress.'</td>
            </tr>';
        }
    
        $tablehtml .= '</table>';
        $mform->addElement('html', $tablehtml);
    }

    public static function get_completion_progress($userid, $lpt_id) {
        $progress = learningpath::progress_of_user( $userid, $lpt_id);
        $class = '';
        if ($progress < 30) {
            $class = 'bg-danger';
        } else if ($progress < 70) {
            $class = 'bg-warning';
        } else {
            $class = 'bg-success';
        }

        $html = '<div class="d-flex align-items-center">
                            <span class="me-2">'.$progress.'%</span>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar {{progress_class}}" role="progressbar" 
                                    aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100" 
                                    style="width: '.$progress.'%;"></div>
                            </div>
                        </div>';
        return $html;
    }
    
    
    public function process_dynamic_submission() {
        $data = parent::get_data();

        $newdata = new stdClass();
        return $newdata;
    }

    public function set_data_for_dynamic_submission(): void {
        $data = (object)$this->_ajaxformdata;
        /*
        if (!empty($data->encodedtable)) {
            $encodedtable = $data->encodedtable;
            $table = wunderbyte_table::instantiate_from_tablecache_hash($encodedtable);
        }
        */

        $this->set_data($data);
    }

    /**
     * Campaings validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     *
     */
    public function validation($data, $files) {
        $errors = [];
        return $errors;
    }

    /**
     * Get page URL for dynamic submission.
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/learningpath/overview.php');
    }

    /**
     * Get context for dynamic submission.
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Check access for dynamic submission.
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        // Perhaps we will need a specific campaigns capability.
        require_capability('local/wunderbyte_table:canedittable', context_system::instance());
    }
}