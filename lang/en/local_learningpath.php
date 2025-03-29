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
 * This plugin serves as a database and path for all learning activities in the organization,
 * where such activities are organized for a more structured learning program.
 * @package    block_learning_path
 * @copyright  3i Logic<lms@3ilogic.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @author     Azmat Ullah <azmat@3ilogic.com>
 */
defined('MOODLE_INTERNAL') || die();
$string['pluginname'] = 'Learning path';
$string['learning_path'] = 'Learning path';
$string['name'] = 'Name';
$string['learningpath'] = 'Add learning path';
$string['desc'] = 'Description';
$string['end_date'] = 'End date';
$string['attachment'] = 'Upload file';
$string['start_date'] = 'Start date';
$string['url'] = 'URL';
$string['add_training'] = 'Add training';
$string['add_training_method'] = 'Add training method';
$string['training_method'] = 'Learning method';
$string['assign_training_learningpath'] = 'Assign training';
$string['training_name'] = 'Course name';
$string['learningpath'] = 'Learning path';
$string['training'] = 'Course name';
$string['add_learningpath'] = 'Add learning path';
$string['trainingstatus'] = 'Set training status';
$string['remarks'] = 'Remarks';
$string['status'] = 'Status';
$string['assign_learningpath_user'] = 'Assign learning path to users';
$string['addusers'] = 'Add users';
$string['searchusers'] = 'Search users';
$string['showuser'] = 'Show users';
$string['block/learning_path:viewpages'] = 'Learning path view pages';
$string['block/learning_path:managepages'] = 'Learning path manage pages';
$string['myview'] = 'My learning path';
$string['search'] = 'Search';
$string['status_report'] = 'Status report';
$string['report_at'] = 'Report as at';
$string['path_format'] = 'Please add learning path in correct format.';
$string['path_exist'] = 'Learning Plan already exist';
$string['training_format'] = 'Please add  training in correct format';
$string['training_exist'] = 'Training already exist.';
$string['date_val'] = 'End date should be greater than start date.';
$string['s_no'] = 'S.No.';
$string['wrong_url'] = 'Wrong URL';
$string['elearning'] = 'eLearning';
$string['classroom'] = 'Classroom training';
$string['onthejob'] = 'On the job training';
$string['assignee'] = 'Assignee';
$string['remove'] = 'Remove';
$string['edit'] = 'Edit';
$string['setting'] = 'Setting';
$string['path_delete'] = 'Do you want to delete learning path?';
$string['training_delete'] = 'Do You Want to Delete training?';
$string['record_delete'] = 'Do you want to delete record?';
$string['learning_path:addinstance'] = 'Add instance';
$string['learning_path:managepages'] = 'Manage pages';
$string['learning_path:viewpages'] = 'View pages';
$string['id'] = 'ID';
$string['user'] = 'User';
$string['users'] = 'Users';
$string['group_selection'] = 'Group selection';
$string['department'] = 'Group';
$string['status_all'] = 'All Status';
$string['status_not_started'] = 'Not Yet Started';
$string['status_in_progress'] = 'In-Progress';
$string['status_completed'] = 'Completed';
$string['status_all'] = 'All Status';
$string['saved'] = 'Record Added';
$string['updated'] = 'Record Updated';
$string['removed'] = 'Record Removed';
$string['saved_changes'] = 'Saved Changes';
$string['select_training'] = 'Please select training(s)';
$string['select_user'] = 'Please select user(s)';
$string['user_training'] = 'Please select training';
$string['selectuser'] = 'Please select user';
$string['select_learningpath'] = 'Please select Learning Plan';
$string['notfound'] = 'Record not found!';
$string['messageprovider:learningpath_notification'] = 'Learning path notification';
$string['learninig_path:learningpath_notification'] = 'View message';
$string['send_notification'] = 'Notification';
$string['message'] = 'Message';
$string['send_message'] = 'Send message';
$string['notification_sent'] = 'Notification sent';
$string['learning_path_error'] = 'Learning path is empty';
$string['messageprovider:view'] = 'View notification';
$string['learninig_path:view'] = 'Learning path notification view';
$string['learninig_path:sendmessages'] = 'Send notification';
$string['learninig_path:viewmessages'] = 'View notification';
$string['newpath'] = 'New Path';
$string['newpathform'] = 'Create New Learning Path';
$string['no'] = 'No.';
$string['pathcreated'] = 'Learning Path created successfully.';
$string['pathupdated'] = 'Learning Path updated successfully.';
$string['learningpathname'] = 'Learning Path Name';
$string['credit'] = 'Credit';
$string['enable_startdate'] = 'Enable Start Date';
$string['startdate'] = 'Start Date';
$string['enable_enddate'] = 'Enable End Date';
$string['enddate'] = 'End Date';
$string['description'] = 'Description';
$string['enableselfenrollment'] = 'Enable Self Enrollment';
$string['learningpathimage'] = 'Learning Path Image';
$string['save'] = 'Save';
$string['cancel'] = 'Cancel';
$string['close'] = 'Close';
$string['savechanges'] = 'Save changes';
$string['traingpath_delete'] = 'Are you sure you want to delete this Learning Path?';
$string['deleted'] = 'Learning Path deleted successfully.';
$string['learningpath'] = 'Learning Paths';
$string['learningpath_desc'] = 'Master topics in small steps. Set a sequence of courses for learners.';
$string['editlearningpath'] = 'Edit Learning Path';
$string['editlearningpath_desc'] = 'Modify details of the selected learning path.';
$string['viewlearningpath'] = 'View Learning Path';
$string['learningpathdetails'] = 'Learning Path Details';
$string['publishsuccess'] = 'The learning path has been successfully published.';
$string['recordnotfound'] = 'The learning path record could not be found.';
$string['addcoursessuccess'] = 'The courses has been successfully saved.';
$string['enrollment'] = 'Enrollment';
$string['enrollmentdate'] = 'Enrollment Date';
$string['email_template'] = 'Email Template';
$string['enrollment_reminder'] = 'Enrollment Reminder';
$string['day_after_enrollment'] = 'Day After Enrollment';
$string['expiration_reminder'] = 'Expiration Reminder';
$string['day_before_expiration'] = 'Day Before Expiration';
$string['day_frequency'] = 'Day Frequency';
$string['completion_path_enable'] = 'Completion Path Enable';
$string['completion_path_mail_templates'] = 'Completion Path Mail Templates';
$string['day_frequency_enable'] = 'Day Frequency Enable';
$string['day_frequency_mail_templates'] = 'Day Frequency Mail Templates';
$string['expiration_enable'] = 'Expiration';
$string['expiration_mail_templates'] = 'Email Template';
$string['expiration_reminder_enable'] = 'Expiration Reminder Enable';
$string['expiration_reminder_mail_templates'] = 'Email Template';
$string['enrollment_enable'] = 'Enrollment Enable';
$string['enrollment_mail_templates'] = 'Email Template';
$string['enrollment_reminder_enable'] = 'Enrollment Reminder Enable';
$string['enrollment_reminder_mail_templates'] = 'Email Template';
$string['day_frequency'] = 'Day Frequency';
$string['day_frequency_mail_templates'] = 'Email Template';
$string['day_frequency_enable'] = 'Day Frequency Enable';
$string['completion_path_enable'] = 'Completion Path Enable';
$string['completion_path_mail_templates'] = 'Completion Path Mail Templates';
$string['tags'] = 'Tags to be used:';
$string['active'] = 'Active';
$string['inactive'] = 'Inactive';
$string['invalidlearningpathid'] = 'Invalid Learning Path ID';
$string['addusertitle'] = 'Add User to Learning Path';
$string['addlinetitle'] = 'Add Line to Learning Path';
$string['addcohorttitle'] = 'Add Cohort to Learning Path';
$string['editsortlinetitle'] = 'Edit Line Sort Order';
$string['sortorder'] = 'Sort Order';
$string['error_positive_number'] = 'Please enter a positive number';
$string['submit'] = 'Submit';
$string['published'] = 'Published';

$string['deletedatatitle'] = 'Delete Record';
$string['deletedatasubmit'] = 'Delete';
$string['deletedatabody'] = 'Are you sure you want to delete this record?';
$string['createnewlearningpath'] = 'Create a new learning path';

$string['close'] = 'Close';
$string['required'] = 'Require';
$string['course'] = 'Course';
$string['catalogue'] = 'Catalogue';
$string['activity'] = 'Activity';
$string['nouseravailable'] = 'No users available.';
$string['addsuccess'] = 'Added successful!';
$string['adderror'] = 'Error adding. Please try again.';
$string['nocohortavailable'] = 'No cohort available.';
$string['errorcohortavailable'] = 'Error loading cohorts. Please try again.';
$string['searchcategory'] = 'Search Category';
$string['allcategories'] = 'All Categories';
$string['recordperpage'] = 'Records Per Page';
$string['nocourseavailable'] = 'No course available.';
$string['errorcourseavailable'] = 'Error loading courses. Please try again.';
$string['nocataloguevailable'] = 'No catalogue available.';
$string['nocatalogueavailable'] = 'Error loading catalogue. Please try again.';
$string['noactivityavailable'] = 'No activity available.';
$string['erroractivityavailable'] = 'Error loading activity. Please try again.';
$string['requiredcredit'] = 'Required credit';
$string['totalcourses'] = 'Total Courses';
$string['totalcohort'] = 'Total Cohorts';
$string['requiredcourse'] = 'Required Courses';
$string['totaluser'] = 'Total Users';
$string['add'] = 'Add';
$string['previous'] = 'Previous';
$string['next'] = 'Next';
$string['cohortreport'] = 'Cohort Report';
$string['nousercohort'] = 'No users found in this cohort.';