<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_learningpath\task\send_notifications',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '1',  // Runs daily at 2 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],
];
