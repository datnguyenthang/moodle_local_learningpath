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

use context;
use context_system;
use core_form\dynamic_form;
use local_wunderbyte_table\wunderbyte_table;
use moodle_url;
use stdClass;
/**
 * Defines the learning path form.
 */
class learningpath_form extends dynamic_form {
    /**
     * Defines the form fields.
     */
    public function definition() {
        $mform = $this->_form;

        $customdata = $this->_customdata;
        $ajaxformdata = $this->_ajaxformdata;
        $data = $ajaxformdata ?? $customdata;

        // Add hidden element for ID to handle edit.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Learning Path Name.
        $mform->addElement('text', 'name', get_string('learningpathname', 'local_learningpath'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Credit.
        $mform->addElement('text', 'credit', get_string('credit', 'local_learningpath'));
        $mform->setType('credit', PARAM_INT);
        $mform->addRule('credit', null, 'required', null, 'client');

        // Start Date Enable Checkbox.
        $mform->addElement('advcheckbox', 'startdate_enable', get_string('enable_startdate', 'local_learningpath'));
        $mform->setDefault('startdate_enable', 0);

        // Start Date.
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_learningpath'));
        $mform->disabledIf('startdate', 'startdate_enable', 'notchecked');

        // End Date Enable Checkbox.
        $mform->addElement('advcheckbox', 'enddate_enable', get_string('enable_enddate', 'local_learningpath'));
        $mform->setDefault('enddate_enable', 0);

        // End Date.
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_learningpath'));
        $mform->disabledIf('enddate', 'enddate_enable', 'notchecked');

        // Description.
        $mform->addElement('editor', 'description_editor', get_string('description', 'local_learningpath'));
        $mform->setType('description_editor', PARAM_RAW); // Allow raw HTML to be saved.

        // Published
        $options = [
            0 => get_string('inactive', 'local_learningpath'),
            1 => get_string('active', 'local_learningpath')
        ];
        
        $mform->addElement('select', 'published', get_string('published', 'local_learningpath'), $options);
        $mform->setType('published', PARAM_INT);
        $mform->setDefault('published', 0); // Default to unpublished

        // Enable Self Enrollment.
        $mform->addElement('advcheckbox', 'enableselfenrollment', get_string('enableselfenrollment', 'local_learningpath'));

        // Learning Path Image.
        $mform->addElement('filemanager', 'learningpathimage', get_string('learningpathimage', 'local_learningpath'), null, [
            'subdirs' => 0,
            'maxbytes' => 10485760, // 10MB
            'areamaxbytes' => 10485760, // 10MB
            'maxfiles' => 1,
            'accepted_types' => array('image')
        ]);

        // Save and Cancel buttons
        $this->add_action_buttons();
    }

    public function process_dynamic_submission() {
        global $DB, $USER;
        $data = parent::get_data();
        
        if (trim($data->startdate_enable) === '0') {
            $data->startdate = null;
        }
    
        if (trim($data->enddate_enable) === "0") {
            $data->enddate = null;
        }
    
        if (isset($data->description_editor)) {
            $data->description = !empty($data->description_editor['text']) ? $data->description_editor['text'] : null;
            unset($data->description_editor);
        }
        $data->timemodified = time();
    
        if (!empty($data->id)) {
            $DB->update_record('local_learningpath', $data);
        } else {
            $data->timecreated = time();
            $data->id = $DB->insert_record('local_learningpath', $data);
        }

        // Adding new learning path photo.
        if (!empty($data->learningpathimage)) {
            $draftitemid = $data->learningpathimage;
            file_save_draft_area_files($draftitemid, \context_system::instance()->id, 'local_learningpath', 'learningpathimage', $data->id, [
                'subdirs' => 0,
                'maxbytes' => 10485760, // 10MB
                'maxfiles' => 1,
                'accepted_types' => ['image']
            ]);
        }

        return $data;
    }

    public function set_data_for_dynamic_submission(): void {
       global $DB;
        $data = (object)$this->_ajaxformdata;
        //var_dump($data->id);
        /*
        if (!empty($data->encodedtable)) {
            $encodedtable = $data->encodedtable;
            $table = wunderbyte_table::instantiate_from_tablecache_hash($encodedtable);
        }
        */       
        $data = $DB->get_record('local_learningpath', ['id' => $data->id]);
        if (!empty($data->description)) 
            $data->description_editor = ['text' => $data->description, 'format' => FORMAT_HTML];
        
        // Load file into draft area.
        $context = \context_system::instance();
        $fs = get_file_storage();
        $draftitemid = file_get_submitted_draft_itemid('learningpathimage');

        file_prepare_draft_area($draftitemid, $context->id, 'local_learningpath', 'learningpathimage', $data->id, [
            'subdirs' => 0,
            'maxbytes' => 10485760,
            'maxfiles' => 1
        ]);
        $data->learningpathimage = $draftitemid;

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
        $errors = parent::validation($data, $files);

        // Check that end date is not before start date.
        if (!empty($data['enddate_enable']) && !empty($data['startdate_enable']) && isset($data['enddate']) && isset($data['startdate']) && $data['enddate'] < $data['startdate']) {
            $errors['enddate'] = get_string('err_enddatebeforestart', 'local_learningpath');
        }

        return $errors;
    }

    /**
     * Get page URL for dynamic submission.
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/learningpath/');
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
        require_capability('local/wunderbyte_table:canedittable', context_system::instance());
    }
}
