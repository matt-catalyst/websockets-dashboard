<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');

$data = array(
    array(
        'id' => '92800',
        'title' => 'Outstanding item for initial setup and theme - Embed object',
        'system' => 'MOJ Totara LMS'
    ),
    array(
        'id' => '81308',
        'title' => 'Update of parent-child course customization for Moodle 2.x',
        'system' => 'St Cuthberts Moodle'
    ),
    array(
        'id' => '94597',
        'title' => 'Record of Learning Broken',
        'system' => 'IRD Moodle Installation'
    )
);


// Wait 1-10 seconds then send random WR data
while(1) {

    $d = $data[rand(0, count($data) - 1)];
    dashboard_push_data('wrms', $d);
    sleep(rand(1, 10));
}
