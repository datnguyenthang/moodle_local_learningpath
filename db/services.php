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
 * Book external functions and service definitions.
 *
 * @package    mod_book
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(
    'local_learningpath_add_line' => array(
        'classname'     => 'local_learningpath_external',
        'methodname'    => 'add_line',
        'classpath'   => 'local/learningpath/classes/external/externallib.php',
        'description'   => 'Add a new line to the learning path',
        'type'          => 'write',
        'ajax'        => true
    ),
    'local_learningpath_remove_line' => array(
        'classname'     => 'local_learningpath_external',
        'methodname'    => 'remove_line',
        'classpath'   => 'local/learningpath/classes/external/externallib.php',
        'description'   => 'Remove a line from the learning path',
        'type'          => 'write',
        'ajax'        => true
    ),
    'local_learningpath_change_line_required' => array(
        'classname'     => 'local_learningpath_external',
        'methodname'    => 'change_line_required',
        'classpath'   => 'local/learningpath/classes/external/externallib.php',
        'description'   => 'Change the required status of a line in the learning path',
        'type'          => 'write',
        'ajax'        => true
    ),

    'local_learningpath_change_line_required' => array(
        'classname'     => 'local_learningpath_external',
        'methodname'    => 'change_line_required',
        'classpath'   => 'local/learningpath/classes/external/externallib.php',
        'description'   => 'Change the required status of a line in the learning path',
        'type'          => 'write',
        'ajax'        => true
    ),

    'local_learningpath_core_course_get_contents' => array(
        'classname' => 'core_course_external',
        'methodname' => 'get_course_contents',
        'classpath' => 'course/externallib.php',
        'description' => 'Get course contents',
        'type' => 'read',
        'ajax' => true
    ),

    'local_learningpath_search_users' => array(
        'classname' => 'local_learningpath_external',
        'methodname' => 'search_users',
        'classpath' => 'local/learningpath/classes/external/externallib.php',
        'description' => 'Search users',
        'type' => 'read',
        'ajax' => true
    ),

    'local_learningpath_add_user' => array(
        'classname' => 'local_learningpath_external',
        'methodname' => 'add_user',
        'classpath' => 'local/learningpath/classes/external/externallib.php',
        'description' => 'Add user',
        'type' => 'write',
        'ajax' => true
    ),

    'local_learningpath_search_cohorts' => array(
        'classname' => 'local_learningpath_external',
        'methodname' => 'search_cohorts',
        'classpath' => 'local/learningpath/classes/external/externallib.php',
        'description' => 'Search cohorts',
        'type' => 'read',
        'ajax' => true
    ),
    'local_learningpath_get_cohorts' => array(
        'classname' => 'local_learningpath_external',
        'methodname' => 'get_cohorts',
        'classpath' => 'local/learningpath/classes/external/externallib.php',
        'description' => 'Get cohorts',
        'type' => 'read',
        'ajax' => true
    ),

    'local_learningpath_add_cohort' => array(
        'classname' => 'local_learningpath_external',
        'methodname' => 'add_cohort',
        'classpath' => 'local/learningpath/classes/external/externallib.php',
        'description' => 'Add cohort',
        'type' => 'write',
        'ajax' => true
    ),
);
