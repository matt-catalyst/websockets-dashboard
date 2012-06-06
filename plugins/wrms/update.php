<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

$lastwr = null;

// Wait 1-10 seconds then send random WR data
while(1) {

    print "API call\n";

    $url = '/api2/report?report_type=request&page_size=20&display_fields=request_id,parent_request_id,status_desc,brief';

    $ch = curl_init($config->url.$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
    curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result);
    $data = $response->response->results;

    // Reverse data
    $data = array_reverse($data);

    foreach ($data as $row) {
        if ($row->request_id <= $lastwr) {
            continue;
        }

        dashboard_push_data('wrms', $row);
        $lastwr = $row->request_id;
    }

    sleep(60);
}
