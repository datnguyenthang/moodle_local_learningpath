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
class learningpath_table extends wunderbyte_table {

    public function col_name($values) {
        return '
                <a href="/local/learningpath/overview.php?id='.$values->id.'" class="btn btn-link text-left">
                    '.$values->name.'
                </a>';
    }

    /**
     * Decodes the Unix Timestamp
     *
     * @param stdClass $values
     * @return string
     */
    public function col_startdate($values) {
        return date('d/m/Y', $values->startdate);
    }

    /**
     * Decodes the Unix Timestamp
     *
     * @param stdClass $values
     * @return void
     */
    public function col_enddate($values) {
        return date('d/m/Y', $values->enddate);
    }

    /**
     * Replace the value of the column with a string.
     *
     * @param stdClass $values
     * @return void
     */
    public function col_published($values) {
        global $OUTPUT;
        $data[] = [
            'label' => '',
            'class' => 'custom-control-input',
            'href' => '#',
            'iclass' => 'fa fa-edit',
            'id' => $values->id.'-'.$this->uniqueid,
            'name' => $this->uniqueid.'-'.$values->id,
            'methodname' => 'togglepublished',
            'ischeckbox' => true,
            'checked' => $values->published ? true : false,
            'disabled' => false,
            'nomodal' => true,
            'data' => [
                'id' => $values->id,
                'state' => $values->published ? 0 : 1,
                'value' => $values->published ? 0 : 1,
            ]
        ];
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
            'formname' => 'local_learningpath\\form\\learningpath_form',
            'nomodal' => true,
            'data' => [
                'id' => $values->id,
                'component' => 'local_learningpath',
                'titlestring' => 'editsortlinetitle',
                'submitbuttonstring' => 'submit',
            ]
        ];

        $data[] = [
            'href' => '#',
            'class' => '',
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
            ]
        ];

        // This transforms the array to make it easier to use in mustache template.
        table::transform_actionbuttons_array($data);

        return $OUTPUT->render_from_template('local_wunderbyte_table/component_actionbutton', ['showactionbuttons' => $data]);
    }

    public function action_deleteitem(int $id, string $data): array {
        global $DB, $FS;
    
        $dataobject = json_decode($data);
    
        // Check if the learning path exists.
        if (!$DB->record_exists('local_learningpath', ['id' => $id])) {
            return [
                'success' => 0,
                'message' => 'The learning path does not exist!',
            ];
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $DB->delete_records('local_learningpath_cohorts', ['lpt_id' => $id]);
            $DB->delete_records('local_learningpath_lines', ['lpt_id' => $id]);
            $DB->delete_records('local_learningpath_users', ['lpt_id' => $id]);
            $DB->delete_records('local_learningpath_notifications', ['lpt_id' => $id]);
    
            $context = \context_system::instance();
            $FS = get_file_storage();
            $FS->delete_area_files($context->id, 'local_learningpath', 'learningpathimage', $id);
    
            $DB->delete_records('local_learningpath', ['id' => $id]);
    
            $transaction->allow_commit();
    
            return [
                'success' => 1,
                'message' => 'This learning path has been deleted!',
            ];
        } catch (Exception $e) {
            $transaction->rollback();
    
            return [
                'success' => 0,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Toggle Checkbox
     *
     * @param int $id
     * @param string $data
     * @return array
     */
    public function action_togglepublished(int $id, string $data):array {
        global $DB;
        $dataobject = json_decode($data);
        $lp = $DB->get_record('local_learningpath', ['id' => $id]);
        $lp->published = $dataobject->value == '0' ? 0 : 1;
        $DB->update_record('local_learningpath', $lp);
        return [
            'success' => 1,
            'message' => $lp->published ? 'This learingpath is now published!' : 'This learingpath is no longer published!',
        ];
    }
}
