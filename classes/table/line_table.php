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
class line_table extends wunderbyte_table {

    public function col_line($values) {
        global $OUTPUT, $DB;
        if ($values->course_id) {
            $course = get_course($values->course_id);
            $link = new \moodle_url('/course/view.php', ['id' => $values->course_id]);
            $label = $course->fullname;
            $icon = 'fa fa-book';
        }
        if ($values->catalogue_id) {
            $catalogue = $DB->get_record('local_catalogue_courses', ['id' => $values->catalogue_id]);
            $link = new \moodle_url('/local/catalogue/detail.php?id='.$values->catalogue_id);
            $label = $catalogue->name;
            $icon = 'fa fa-list';
        }
        if ($values->module_id) {
            $activity = get_coursemodule_from_id(null, $values->module_id);
            $link = new \moodle_url('/mod/'.$activity->modname.'/view.php', ['id' => $values->module_id]);
            $label = $activity->name;
            $icon = 'fa fa-cog';
        }
        $data[] = [
            'class' => '',
            'href' => $link,
            'iclass' => $icon,
            'arialabel' => 'cogwheel',
            'label' => $label,
            'title' => $label,
            'target' => '_blank',
            'id' => $values->id.'-'.$this->uniqueid,
            'name' => $this->uniqueid.'-'.$values->id,
            'methodname' => false,
            'ischeckbox' => false,
            'nomodal' => false,
            'data' => [
                'id' => $values->id,
            ]
        ];
        // This transforms the array to make it easier to use in mustache template.
        table::transform_actionbuttons_array($data);

        return $OUTPUT->render_from_template('local_wunderbyte_table/component_actionbutton', ['showactionbuttons' => $data]);
    }

    public function col_credit($values) {
        $values->credit = 0;
        return $values->credit;
    }

    public function col_required($values) {
        global $OUTPUT;
        \cache_helper::purge_by_event('changesinwunderbytetable');
        $data[] = [
            'label' => '',
            'class' => 'custom-control-input',
            'href' => '#',
            'iclass' => 'fa fa-edit',
            'id' => $values->id.'-'.$this->uniqueid,
            'name' => $this->uniqueid.'-'.$values->id,
            'methodname' => 'togglerequired',
            'ischeckbox' => true,
            'checked' => $values->required ? true : false,
            'disabled' => false,
            'nomodal' => true,
            'data' => [
                'id' => $values->id,
                'state' => $values->required ? 0 : 1,
                'value' => $values->required ? 0 : 1,
            ]
        ];

        // This transforms the array to make it easier to use in mustache template.
        table::transform_actionbuttons_array($data);

        return $OUTPUT->render_from_template('local_wunderbyte_table/component_actiontoggle', ['showactionbuttons' => $data]);
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
            'iclass' => 'fa fa-edit',
            'arialabel' => 'cogwheel',
            'title' => 'Edit',
            'id' => $values->id.'-'.$this->uniqueid,
            'name' => $this->uniqueid.'-'.$values->id,
            'formname' => 'local_learningpath\\form\\line_sort_form',
            'nomodal' => true,
            'data' => [
                'id' => $values->id,
                'component' => 'local_learningpath',
                'titlestring' => 'editsortlinetitle',
                'submitbuttonstring' => 'submit',
            ]
        ];

        $data[] = [
            'class' => '',
            'href' => '#',
            'iclass' => 'fa fa-trash',
            'arialabel' => 'cogwheel',
            'title' => 'Delete',
            'id' => $values->id.'-'.$this->uniqueid,
            'name' => $this->uniqueid.'-'.$values->id,
            'methodname' => 'deleteitem',
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

    public function action_deleteitem(int $id, string $data):array {
        global $DB;
        //\cache_helper::purge_by_event('changesinwunderbytetable');
        $dataobject = json_decode($data);
        $DB->delete_records('local_learningpath_lines', ['id' => $id]);
        return [
            'success' => 1,
            'message' => 'This line has been deleted!',
        ];
    }

    /**
     * Toggle Checkbox
     *
     * @param int $id
     * @param string $data
     * @return array
     */
    public function action_togglerequired(int $id, string $data):array {
        global $DB;
        $dataobject = json_decode($data);
        $line = $DB->get_record('local_learningpath_lines', ['id' => $id]);
        $line->required = $dataobject->value == '0' ? 0 : 1;
        $DB->update_record('local_learningpath_lines', $line);
        return [
            'success' => 1,
            'message' => $line->required ? 'This line is now required!' : 'This line is no longer required!',
        ];
    }

}
