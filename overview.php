<?php
require_once('../../config.php');
require_once('./lib.php');
require_once('./classes/form/notification_form.php');
global $DB, $USER, $OUTPUT, $PAGE;

$id = optional_param ('id', null, PARAM_INT);
$action = optional_param('action', null, PARAM_RAW);
$rem = optional_param('rem', null, PARAM_RAW);

require_login();
$pageurl = new moodle_url('/local/learningpath/overview.php', array('id' => $id));
$PAGE->set_context(context_system::instance());
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('');
$PAGE->set_title(get_string('learningpathdetails', 'local_learningpath'));
$PAGE->requires->css(new moodle_url('/local/learningpath/style.css'));

if (!$record = $DB->get_record('local_learningpath', ['id' => $id])) {
    throw new \moodle_exception('invalidlearningpathid', 'local_learningpath');
}

$notification_form = new \local_learningpath\form\notification_form(null, ['lpt_id' => $id]);

$line = new \local_learningpath\output\line();
$user = new \local_learningpath\output\user();
$cohort = new \local_learningpath\output\cohort();

$templatecontext = [
    'name' => $record->name,
    'backurl' => new moodle_url('/local/learningpath/index.php'),
    'overview_content' => $OUTPUT->render_from_template('local_learningpath/overview_tab', [
        'image' => get_learningpath_image_url($id),
        'startdate' => isset($record->startdate) ? date('d-m-Y', $record->startdate) : null,
        'enddate' => isset($record->enddate) ? date('d-m-Y', $record->enddate) : null,
        'totalusers' => 1,
        'requiredcredit' => $record->credit ?? 0,
        'totalcourses' => 3,
        'totalcohorts' => 0,
        'requiredcourses' => 3,
        'description' => format_text($record->description, FORMAT_HTML, ['noclean' => true]),
    ]),
    'lines_content' => $OUTPUT->render_from_template('local_learningpath/lines_tab', [
        'lines_table' => $line->render_table($id),
    ]),
    'users_content' => $OUTPUT->render_from_template('local_learningpath/users_tab', [
        'user_table' => $user->render_table($id),
    ]),
    'cohorts_content' => $OUTPUT->render_from_template('local_learningpath/cohorts_tab', [
        'cohorts_table' => $cohort->render_table($id),
    ]),
    'notifications_content' => $OUTPUT->render_from_template('local_learningpath/notifications_tab', [
        'notification_id' => 'notificationform',
        'notification_form_html' => $notification_form->render(),
        //'notification_class' => 'local_learningpath\\form\\notification_form',
        'lpt_id' => $id
    ]),
    'tabs' => [
        ['url' => '#Overview', 'label' => 'Overview', 'active' => true],
        ['url' => '#Lines', 'label' => 'Lines'],
        ['url' => '#Users', 'label' => 'Users'],
        ['url' => '#Cohorts', 'label' => 'Cohorts'],
        ['url' => '#Notifications', 'label' => 'Notifications'],
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningpath/overview', $templatecontext);
echo $OUTPUT->footer();