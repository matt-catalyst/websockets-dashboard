<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

// Cache last 20 WRs to check for changed WRs
$lastwrs = array();

// Cols we care about
$cols = array(
    'request_id',
    'last_activity_epoch',
    'organisation_code',
    'system_name',
    'system_code',
    'status_desc',
    'brief',
    'ranking'
);

// Base url
$url = $config->url.
        '/api2/report'.
        '?report_type=request'.
        '&page_size=100'.
        '&display_fields='.implode(',', $cols).
        '&interested_users='.$config->interested_user;

$reports = array(
    '&last_status=N&order_by=last_activity_epoch&order_direction=desc',
    '&last_status=N&order_by=last_activity_epoch&order_direction=desc&page_no=2',
    '&last_status=N&order_by=last_activity_epoch&order_direction=desc&page_no=3',
    '&last_status=L&order_by=ranking&order_direction=desc'
);

// Wait 1-10 seconds then send random WR data
while(1) {

    print "API call\n";

    // Number "seen"
    $seen = 0;
    $send = false;

    foreach ($reports as $report) {
        $ch = curl_init($url.$report);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);
        $data = $response->response->results;

        foreach ($data as $i => $row) {

            // If that's 20 new/edited/unchanged, break
            if ($seen >= 20) {
                break 2;
            }

            // Check if ignored system
            if (!empty($config->ignore_orgs)) {
                if (in_array($row->organisation_code, $config->ignore_orgs)) {
                    continue;
                }
            }

            // Add URL
            $row->request_url = "{$config->url}/wr.php?request_id={$row->request_id}";

            // Check if it doesn't match what is in $lastwrs
            if (!isset($lastwrs[$seen]) || $row != $lastwrs[$seen]) {
                // Update $lastwrs and trigger sending of new version
                $send = true;
                $lastwrs[$seen] = $row;
            }

            ++$seen;
        }
    }

    if ($send) {
        print "Update sent\n";
        // Reverse order of WRs (as they will be prepended one at a time) and
        // pad the array to flush out the cache
        dashboard_push_data('wrms', array_pad(array_reverse($lastwrs), -20, 1), $multiple=true);
    }

    sleep(60);
}
