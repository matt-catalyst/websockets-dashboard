<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

// Wait 1-10 seconds then send random WR data
while(1) {

    print "API call\n";

    // Data to send
    $send = array();

    foreach ($config->people as $name => $id) {
        $user = new stdClass();
        $fullname = explode(' ', $name);
        $user->name = "{$fullname[0]} {$fullname[1][0]}";
        $user->id = $id;
        $user->image = $config->photohost.str_replace(' ', '_', strtolower($name)).'.jpg';

        // Get data from WRMS
        $url = '/api2/report?report_type=request&page_size=200&display_fields=request_id&allocated_to='.$user->id;

        // Allocated
        $ch = curl_init($config->url.$url.'&last_status=L');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);
        $user->allocated = count($response->response->results);

        // Allocated
        $ch = curl_init($config->url.$url.'&last_status=I');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);
        $user->inprogress = count($response->response->results);
        $send[] = $user;
    }

    print count($send)." users sent\n";
    dashboard_push_data('availability', $send, $multiple=true);

    sleep(300);
}
