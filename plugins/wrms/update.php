<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

$lastwr = null;

// Wait 1-10 seconds then send random WR data
while(1) {

    print "API call\n";

    $url = '/api2/report?report_type=request&page_size=20&display_fields=request_id,system_code,status_desc,brief&interested_users='.$config->interested_user;

    $ch = curl_init($config->url.$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
    curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result);
    $data = $response->response->results;

    // Reverse data
    $data = array_reverse($data);

    // Data to send
    $send = array();

    foreach ($data as $row) {
        if ($row->request_id <= $lastwr) {
            continue;
        }

        if (!empty($config->ignore_systems)) {
            if (in_array($row->system_code, $config->ignore_systems)) {
                continue;
            }
        }

        $row->request_url = "{$config->url}/wr.php?request_id={$row->request_id}";
        $send[] = $row;
    }

    if (!empty($send)) {
        print count($send)." updates sent\n";
        dashboard_push_data('wrms', $send, $multiple=true);
        $lastwr = $row->request_id;
    }

    sleep(60);
}
