<?php

$data = array(
    array('92800' => array('title' => 'Outstanding item for initial setup and theme - Embed object', 'system' => 'MOJ Totara LMS')),
    array('81308' => array('title' => 'Update of parent-child course customization for Moodle 2.x', 'system' => 'St Cuthberts Moodle')),
    array('94597' => array('title' => 'Record of Learning Broken', 'system' => 'IRD Moodle Installation'))
);

$destination = array('wrms', 'example');


// Wait 1-10 seconds then send random WR data
while(1) {

    $json = json_encode($data[rand(0, count($data) - 1)]);
    $d = '_xsrf=0&data='.urlencode($json);

    $plugin = $destination[rand(0, 1)];

    $ch = curl_init("http://127.0.0.1:8888/update/{$plugin}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
    curl_exec($ch);
    curl_close($ch);

    sleep(rand(1, 10));
}
