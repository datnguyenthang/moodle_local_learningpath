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
require_once('../../config.php');
global $DB, $USER, $OUTPUT, $PAGE, $CFG;

$id = optional_param('id', null, PARAM_INT);
$pageurl = new moodle_url('/local/learningpath/', array());

$PAGE->set_context(context_system::instance());
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('learning_path', 'local_learningpath'));

$learningpaths = new \local_learningpath\output\learningpath();

$templatecontext = [
    'title' => get_string('learningpath', 'local_learningpath'),
    'description' => get_string('desc'),
    'learningpaths_table' => $learningpaths->render_table(),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningpath/index', $templatecontext);
echo $OUTPUT->footer();