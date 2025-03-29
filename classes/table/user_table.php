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

use block_learningpath\learningpath;

/**
 * Wunderbyte table demo class.
 */
class user_table extends wunderbyte_table {

    public function col_timecreated($values) {
        return date('d/m/Y', $values->time);
    }

    public function col_progress_completed($values) {
        $progress = learningpath::progress_of_user($values->u_id, $values->lpt_id);
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
            'methodname' => 'deleteuser',
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

    public function action_deleteuser(int $id, string $data):array {
        global $DB;
        $dataobject = json_decode($data);
        $DB->delete_records('local_learningpath_users', ['id' => $id]);
        return [
            'success' => 1,
            'message' =>  'User deleted successfully!'
        ];
    }

}
