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
use moodle_url;
use stdClass;
/**
 * Defines the learning path form.
 */
class notification_form extends dynamic_form {

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

        $mform->addElement('hidden', 'lpt_id');
        $mform->setType('lpt_id', PARAM_INT);
        $mform->setDefault('lpt_id', $data['lpt_id']);

        // Enrollment.
        $mform->addElement('advcheckbox', 'enrollment_enable', get_string('enrollment', 'local_learningpath'));
        $mform->setType('enrollment_enable', PARAM_INT);
        $mform->addElement('editor', 'enrollment_mail_templates', get_string('email_template', 'local_learningpath'), null, array());
        $mform->setType('enrollment_mail_templates', PARAM_TEXT);
       
        $mform->addElement('textarea', 'enrollment_tags', get_string('tags', 'local_learningpath'));
        $mform->setType('enrollment_tags', PARAM_TEXT);
        $mform->setDefault('enrollment_tags', '{user_fullname}, {learningpath_name}, {learningpath_startdate}, {learningpath_enddate}, {learningpath_coursesrequired}');
        $mform->freeze('enrollment_tags');

        // Expiration
        $mform->addElement('advcheckbox', 'expiration_enable', get_string('expiration_enable', 'local_learningpath'));
        $mform->setDefault('expiration_enable', 0);
        $mform->addElement('editor', 'expiration_mail_templates', get_string('email_template', 'local_learningpath'), null, array());
        $mform->setType('expiration_mail_templates', PARAM_TEXT);

        $mform->addElement('textarea', 'expiration_tags', get_string('tags', 'local_learningpath'));
        $mform->setType('expiration_tags', PARAM_TEXT);
        $mform->setDefault('expiration_tags', '{user_fullname}, {learningpath_name}, {learningpath_startdate}, {learningpath_enddate}, {learningpath_coursesrequired}');
        $mform->freeze('expiration_tags');

        // Enrollment reminder
        $mform->addElement('advcheckbox', 'enrollment_reminder_enable', get_string('enrollment_reminder', 'local_learningpath'));
        $mform->setType('enrollment_reminder_enable', PARAM_INT);
        $mform->addElement('text', 'day_after_enrollment', get_string('day_after_enrollment', 'local_learningpath'));
        $mform->addRule('day_after_enrollment', null, 'numeric', null, 'client');
        $mform->setType('day_after_enrollment', PARAM_INT);
        $mform->addElement('editor', 'enrollment_reminder_mail_templates', get_string('email_template', 'local_learningpath'), null, array());
        $mform->setType('enrollment_reminder_mail_templates', PARAM_TEXT);

        $mform->addElement('textarea', 'enrollment_reminder_tags', get_string('tags', 'local_learningpath'));
        $mform->setType('enrollment_reminder_tags', PARAM_TEXT);
        $mform->setDefault('enrollment_reminder_tags', '{user_fullname}, {learningpath_name}, {learningpath_startdate}, {learningpath_enddate}, {learningpath_coursesrequired}');
        $mform->freeze('enrollment_reminder_tags');

        // Expiration reminder
        $mform->addElement('advcheckbox', 'expiration_reminder_enable', get_string('expiration_reminder', 'local_learningpath'));
        $mform->setType('expiration_reminder_enable', PARAM_INT);
        $mform->addElement('text', 'day_before_expiration', get_string('day_before_expiration', 'local_learningpath'));
        $mform->addRule('day_before_expiration', null, 'numeric', null, 'client');
        $mform->setType('day_before_expiration', PARAM_INT);
        $mform->addElement('editor', 'expiration_reminder_mail_templates', get_string('email_template', 'local_learningpath'), null, array());
        $mform->setType('expiration_reminder_mail_templates', PARAM_TEXT);
        
        $mform->addElement('textarea', 'expiration_reminder_tags', get_string('tags', 'local_learningpath'));
        $mform->setType('expiration_reminder_tags', PARAM_TEXT);
        $mform->setDefault('expiration_reminder_tags', '{user_fullname}, {learningpath_name}, {learningpath_startdate}, {learningpath_enddate}, {learningpath_coursesrequired}');
        $mform->freeze('expiration_reminder_tags');

        // Day Frequency
        $mform->addElement('advcheckbox', 'day_frequency_enable', get_string('day_frequency', 'local_learningpath'));
        $mform->setType('day_frequency_enable', PARAM_INT);
        $mform->addElement('text', 'day_frequency', get_string('day_frequency', 'local_learningpath'));
        $mform->addRule('day_frequency', null, 'numeric', null, 'client');
        $mform->setType('day_frequency', PARAM_INT);
        $mform->addElement('editor', 'day_frequency_mail_templates', get_string('email_template', 'local_learningpath'), null, array());
        $mform->setType('day_frequency_mail_templates', PARAM_TEXT);
        
        $mform->addElement('textarea', 'day_frequency_tags', get_string('tags', 'local_learningpath'));
        $mform->setType('day_frequency_tags', PARAM_TEXT);
        $mform->setDefault('day_frequency_tags', '{user_fullname}, {learningpath_name}, {learningpath_startdate}, {learningpath_enddate}, {learningpath_coursesrequired}');
        $mform->freeze('day_frequency_tags');

        // Completion learningpath
        $mform->addElement('advcheckbox', 'completion_path_enable', get_string('completion_path_enable', 'local_learningpath'));
        $mform->setType('completion_path_enable', PARAM_INT);
        $mform->addElement('editor', 'completion_path_mail_templates', get_string('email_template', 'local_learningpath'), null, array());
        $mform->setType('completion_path_mail_templates', PARAM_TEXT);
        
        $mform->addElement('textarea', 'completion_path_tags', get_string('tags', 'local_learningpath'));
        $mform->setType('completion_path_tags', PARAM_TEXT);
        $mform->setDefault('completion_path_tags', '{user_fullname}, {learningpath_name}, {learningpath_startdate}, {learningpath_enddate}, {learningpath_coursesrequired}');
        $mform->freeze('completion_path_tags');

        // Save and Cancel buttons
        $this->add_action_buttons();

        $mform->disable_form_change_checker();
    }
    
    public function process_dynamic_submission() {
        global $DB;
        $data = parent::get_data();

        $notification = $DB->get_record('local_learningpath_notifications', ['lpt_id' => $data->lpt_id]);

        if (!$notification) {
            $notification = new stdClass();
        }

        $notification->lpt_id = $data->lpt_id;
        $notification->enrollment_enable = $data->enrollment_enable;
        $notification->enrollment_mail_templates = isset($data->enrollment_mail_templates["text"]) ? $data->enrollment_mail_templates["text"] : null;
        $notification->expiration_enable = $data->expiration_enable;
        $notification->expiration_mail_templates = isset($data->expiration_mail_templates["text"]) ? $data->expiration_mail_templates["text"] : null;
        $notification->enrollment_reminder_enable = $data->enrollment_reminder_enable;
        $notification->day_after_enrollment = $data->day_after_enrollment;
        $notification->enrollment_reminder_mail_templates = isset($data->enrollment_reminder_mail_templates["text"]) ? $data->enrollment_reminder_mail_templates["text"] : null;
        $notification->expiration_reminder_enable = $data->expiration_reminder_enable;
        $notification->day_before_expiration = $data->day_before_expiration;
        $notification->expiration_reminder_mail_templates = isset($data->expiration_reminder_mail_templates["text"]) ? $data->expiration_reminder_mail_templates["text"] : null;
        $notification->day_frequency_enable = $data->day_frequency_enable;
        $notification->day_frequency = $data->day_frequency;
        $notification->day_frequency_mail_templates = isset($data->day_frequency_mail_templates["text"]) ? $data->day_frequency_mail_templates["text"] : null;
        $notification->completion_path_enable = $data->completion_path_enable;
        $notification->completion_path_mail_templates = isset($data->completion_path_mail_templates["text"]) ? $data->completion_path_mail_templates["text"] : null;

        if ($notification->id) {
            $data->id = $notification->id;
            $DB->update_record('local_learningpath_notifications', $notification);
        } else {
            $data->id = $DB->insert_record('local_learningpath_notifications', $notification);
        }
    }

    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $data = (object)$this->_customdata;
        $notification = $DB->get_record('local_learningpath_notifications', ['lpt_id' => $data->lpt_id]);

        if (!empty($notification)) {
            $notification->enrollment_mail_templates = ['text' => $notification->enrollment_mail_templates, 'format' => FORMAT_HTML];
            $notification->expiration_mail_templates = ['text' => $notification->expiration_mail_templates, 'format' => FORMAT_HTML];
            $notification->enrollment_reminder_mail_templates = ['text' => $notification->enrollment_reminder_mail_templates, 'format' => FORMAT_HTML];
            $notification->expiration_reminder_mail_templates = ['text' => $notification->expiration_reminder_mail_templates, 'format' => FORMAT_HTML];
            $notification->day_frequency_mail_templates = ['text' => $notification->day_frequency_mail_templates, 'format' => FORMAT_HTML];
            $notification->completion_path_mail_templates = ['text' => $notification->completion_path_mail_templates, 'format' => FORMAT_HTML];

            $this->set_data($notification);
        }
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

    public function definition_after_data() {
        $mform = $this->_form;

        // Enrollment.
        if (!$mform->getElementValue('enrollment_enable')) {
            $mform->hideIf('enrollment_mail_templates', 'enrollment_enable', 'notchecked');
            $mform->hideIf('enrollment_tags', 'enrollment_enable', 'notchecked');
        }

        // Expiration.
        if (!$mform->getElementValue('expiration_enable')) {
            $mform->hideIf('expiration_mail_templates', 'expiration_enable', 'notchecked');
            $mform->hideIf('expiration_tags', 'expiration_enable', 'notchecked');
        }

        // Enrollment reminder.
        if (!$mform->getElementValue('enrollment_reminder_enable')) {
            $mform->hideIf('day_after_enrollment', 'enrollment_reminder_enable', 'notchecked');
            $mform->hideIf('enrollment_reminder_mail_templates', 'enrollment_reminder_enable', 'notchecked');
            $mform->hideIf('enrollment_reminder_tags', 'enrollment_reminder_enable', 'notchecked');
        }

        // Expiration reminder.
        if (!$mform->getElementValue('expiration_reminder_enable')) {
            $mform->hideIf('day_before_expiration', 'expiration_reminder_enable', 'notchecked');
            $mform->hideIf('expiration_reminder_mail_templates', 'expiration_reminder_enable', 'notchecked');
            $mform->hideIf('expiration_reminder_tags', 'expiration_reminder_enable', 'notchecked');
        }

        // Day Frequency.
        if (!$mform->getElementValue('day_frequency_enable')) {
            $mform->hideIf('day_frequency', 'day_frequency_enable', 'notchecked');
            $mform->hideIf('day_frequency_mail_templates', 'day_frequency_enable', 'notchecked');
            $mform->hideIf('day_frequency_tags', 'day_frequency_enable', 'notchecked');
        }

        // Completion learningpath.
        if (!$mform->getElementValue('completion_path_enable')) {
            $mform->hideIf('completion_path_mail_templates', 'completion_path_enable', 'notchecked');
            $mform->hideIf('completion_path_tags', 'completion_path_enable', 'notchecked');
        }
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
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }
}