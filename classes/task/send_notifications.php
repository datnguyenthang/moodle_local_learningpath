<?php
namespace local_learningpath\task;

use core\task\scheduled_task;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

class send_notifications extends scheduled_task {
    public function get_name() {
        return get_string('sendnotifications', 'local_learningpath');
    }

    public function execute() {
        global $DB;

        // Get all notifications where at least one feature is enabled
        $notifications = $DB->get_records_sql("
            SELECT * FROM {local_learningpath_notifications} 
            WHERE enrollment_enable = 1 
               OR expiration_enable = 1 
               OR day_frequency_enable = 1 
               OR completion_path_enable = 1
        ");

        foreach ($notifications as $notification) {
            $this->process_notification($notification);
        }
    }

    private function process_notification($notification) {
        global $DB;

        // Get users enrolled in the learning path
        $users = $DB->get_records('local_learningpath_users', ['lpt_id' => $notification->lpt_id]);

        foreach ($users as $user) {
            $this->check_enrollment_reminder($user, $notification);
            $this->check_expiration_reminder($user, $notification);
            $this->check_frequency_notification($user, $notification);
            $this->check_completion_notification($user, $notification);
        }
    }

    private function check_enrollment_reminder($user, $notification) {
        global $DB;

        if ($notification->enrollment_reminder_enable && $notification->day_after_enrollment) {
            $enrollment_date = $DB->get_field('local_learningpath_users', 'enrollment_date', ['lpt_id' => $notification->lpt_id, 'userid' => $user->userid]);

            if ($enrollment_date) {
                $days_since_enrollment = (time() - $enrollment_date) / 86400;

                if ($days_since_enrollment == $notification->day_after_enrollment) {
                    $message = $this->replace_placeholders($notification->enrollment_reminder_mail_templates, $user, $notification);
                    $this->send_email($user->userid, 'Enrollment Reminder', $message);
                }
            }
        }
    }

    private function check_expiration_reminder($user, $notification) {
        global $DB;

        if ($notification->expiration_reminder_enable && $notification->day_before_expiration) {
            $expiration_date = $DB->get_field('local_learningpath_users', 'expiration_date', ['lpt_id' => $notification->lpt_id, 'userid' => $user->userid]);

            if ($expiration_date) {
                $days_left = ($expiration_date - time()) / 86400;

                if ($days_left == $notification->day_before_expiration) {
                    $message = $this->replace_placeholders($notification->expiration_reminder_mail_templates, $user, $notification);
                    $this->send_email($user->userid, 'Expiration Reminder', $message);
                }
            }
        }
    }

    private function check_frequency_notification($user, $notification) {
        global $DB;

        if ($notification->day_frequency_enable && $notification->day_frequency) {
            $last_sent = $DB->get_field('local_learningpath_users', 'last_notification_sent', ['lpt_id' => $notification->lpt_id, 'userid' => $user->userid]);

            if (!$last_sent || (time() - $last_sent) >= ($notification->day_frequency * 86400)) {
                $message = $this->replace_placeholders($notification->day_frequency_mail_templates, $user, $notification);
                $this->send_email($user->userid, 'Regular Notification', $message);

                // Update last notification sent time
                $DB->set_field('local_learningpath_users', 'last_notification_sent', time(), ['lpt_id' => $notification->lpt_id, 'userid' => $user->userid]);
            }
        }
    }

    private function check_completion_notification($user, $notification) {
        if ($notification->completion_path_enable) {
            $message = $this->replace_placeholders($notification->completion_path_mail_templates, $user, $notification);
            $this->send_email($user->userid, 'Completion Path Notification', $message);
        }
    }

    private function send_email($userid, $subject, $message) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/moodlelib.php');

        $user = $DB->get_record('user', ['id' => $userid]);
        if ($user) {
            email_to_user($user, get_admin(), $subject, strip_tags($message), $message);
        }
    }

    /**
     * Replaces placeholders in the email template with actual values
     */
    private function replace_placeholders($message, $user, $notification) {
        global $DB;

        // Get user full name
        $userdata = $DB->get_record('user', ['id' => $user->userid]);
        $userfullname = fullname($userdata);

        // Get learning path details
        $learningpath = $DB->get_record('local_learningpath', ['id' => $notification->lpt_id]);
        $learningpath_name = $learningpath->name ?? 'Unknown Learning Path';
        $learningpath_startdate = $learningpath->startdate ? date('d M Y', $learningpath->startdate) : 'Not set';
        $learningpath_enddate = $learningpath->enddate ? date('d M Y', $learningpath->enddate) : 'Not set';

        // Count required courses in the learning path
        $coursesrequired = $DB->count_records('local_learningpath_courses', ['lpt_id' => $notification->lpt_id]);

        // Replace placeholders in the message
        $message = str_replace('{user_fullname}', $userfullname, $message);
        $message = str_replace('{learningpath_name}', $learningpath_name, $message);
        $message = str_replace('{learningpath_startdate}', $learningpath_startdate, $message);
        $message = str_replace('{learningpath_enddate}', $learningpath_enddate, $message);
        $message = str_replace('{learningpath_coursesrequired}', $coursesrequired, $message);

        return $message;
    }
}
