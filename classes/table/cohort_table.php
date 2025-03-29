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
 * The Wunderbyte table class is an extension of the tablelib table_sql class.
 *
 * @package local_wunderbyte_table
 * @copyright 2023 Wunderbyte Gmbh <info@wunderbyte.at>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:ignoreFile

namespace local_learningpath\table;

defined('MOODLE_INTERNAL') || die();

use local_wunderbyte_table\output\table;
use local_wunderbyte_table\wunderbyte_table;
use stdClass;

/**
 * Wunderbyte table demo class.
 */
class cohort_table extends wunderbyte_table {

    public function col_timecreated($values) {
        return date('d/m/Y', $values->time);
    }

    public function col_numberofuser($values) {
        global $DB;
        return $DB->count_records('cohort_members', ['cohortid' => $values->cohort_id]);
    }

    public function col_name($values) {
        global $OUTPUT;
        $data[] = [
            'class' => '',
            'href' => '#',
            'iclass' => '',
            'arialabel' => 'cogwheel',
            'title' => '',
            'label' => $values->name,
            //'id' => $values->id.'-'.$this->uniqueid,
            'id' => $values->lpt_id.'-'.$values->cohort_id,
            'name' => $this->uniqueid.'-'.$values->id,
            'formname' => 'local_learningpath\\form\\cohort_report',
            'nomodal' => true,
            'data' => [
                'id' => $values->id,
                //'lpt_id' => $values->lpt_id,
                //'cohort_id' => $values->id,
                'submitbuttonstring' => 'submit',
                'titlestring' => 'cohortreport',
                'component' => 'local_learningpath',
            ]
        ];

        table::transform_actionbuttons_array($data);

        return $OUTPUT->render_from_template('local_wunderbyte_table/component_actionbutton', ['showactionbuttons' => $data]);
    }

    public function col_progress_completed($values) {
        $percentage = 70; //$values->progress;
        $class = '';
        if ($percentage < 30) {
            $class = 'bg-danger';
        } else if ($percentage < 70) {
            $class = 'bg-warning';
        } else {
            $class = 'bg-success';
        }

        $html = '<div class="progress">
                    <div class="progress-bar '.$class.'" role="progressbar" aria-valuenow="'.$percentage.'"
                        aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%">
                            '.$percentage.'%
                    </div>
                </div>';
        return $html;
    }

    /**
     * This handles the action column with buttons, icons, checkboxes.
     *
     * @param stdClass $values
     * @return void
     */
    public function col_action($values) {
        global $OUTPUT;

        $data[] = [
            'class' => '',
            'href' => '#',
            'iclass' => 'fa fa-trash',
            'arialabel' => 'cogwheel',
            'title' => 'Delete',
            'id' => $values->id.'-'.$this->uniqueid,
            'name' => $this->uniqueid.'-'.$values->id,
            'methodname' => 'deletecohort',
            'ischeckbox' => false,
            'nomodal' => false,
            'data' => [
                'id' => $values->id,
                'titlestring' => 'deletedatatitle',
                'bodystring' => 'deletedatabody',
                'submitbuttonstring' => 'deletedatasubmit',
                'component' => 'local_learningpath',
                'noselectionbodystring' => 'specialbody',
            ]
        ];

        // This transforms the array to make it easier to use in mustache template.
        table::transform_actionbuttons_array($data);

        return $OUTPUT->render_from_template('local_wunderbyte_table/component_actionbutton', ['showactionbuttons' => $data]);
    }

    public function action_deletecohort(int $id, string $data):array {
        global $DB;
        $dataobject = json_decode($data);
        $DB->delete_records('local_learningpath_cohorts', ['id' => $id]);
        return [
            'success' => 1,
            'message' =>  'User deleted successfully!'
        ];
    }

}
