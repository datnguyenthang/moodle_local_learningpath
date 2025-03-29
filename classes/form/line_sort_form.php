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
/**
 * Defines the learning path form.
 */
class line_sort_form extends dynamic_form {

    /**
     * {@inheritdoc}
     * @see moodleform::definition()
     */
    public function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $customdata = $this->_customdata;
        $ajaxformdata = $this->_ajaxformdata;

        $data = $ajaxformdata ?? $customdata;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'sortorder', get_string('sortorder', 'local_learningpath'));
        $mform->setType('sortorder', PARAM_INT);
        $mform->addRule('sortorder', null, 'numeric', null, 'client');
        $mform->addRule('sortorder', get_string('error_positive_number', 'local_learningpath'), 'regex', '/^\d+$/', 'client');

        $mform->disable_form_change_checker();
    }
    
    public function process_dynamic_submission() {
        global $DB;
        $data = parent::get_data();
        $line = $DB->get_record('local_learningpath_lines', ['id' => $data->id]);
        $line->sortorder = $data->sortorder;
        $DB->update_record('local_learningpath_lines', $line);
        return $line;
    }

    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $data = (object)$this->_ajaxformdata;
        if($data->id) {
            $line = $DB->get_record('local_learningpath_lines', ['id' => $data->id]);
            $data->sortorder = $line->sortorder;
        }

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
        return new moodle_url('/local/learningpath/overview.php', ['id' => $this->_customdata['lpt_id']]);
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