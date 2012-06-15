<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

// Wait 1-10 seconds then send random WR data
while(1) {

    print "API call\n";

    $dayofweek = date('N');
    $expectedhourstw = min($dayofweek-1, 4) * 8;  // todo pub holiday shiz
    $expectedhourslw = 40;  // todo pub holidays, etc.

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

        // In progress
        $ch = curl_init($config->url.$url.'&last_status=I');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);
        $user->inprogress = count($response->response->results);

        // Hours last week
        $user->hourslastweek = timesheets_get_total_hours($user, 'w-1%3Aw-1');
        if ($user->hourslastweek === false) {
            sleep(300);
            break;
        }
        // Only shame the fools on Mon, Tue and Wed
        $user->shamedlastweek = (in_array($dayofweek, array(1, 2, 3)) && $user->hourslastweek < $expectedhourslw);

        // Hours this week
        $user->hoursthisweek = timesheets_get_total_hours($user, 'w%3Aw');
        if ($user->hoursthisweek === false) {
            sleep(300);
            break;
        }
        $user->shamedthisweek = ($user->hoursthisweek < $expectedhourstw);

        $send[] = $user;
    }

    print count($send)." users sent\n";
    dashboard_push_data('availability', $send, $multiple=true);

    sleep(300);
}

function timesheets_get_total_hours($user, $daterange) {
    include(dirname(__FILE__).'/config.php');

    // Get data from WRMS
    $url = '/api2/report?report_type=timesheet&page_size=200&display_fields=hours_sum&order_by=request_id&order_direction=desc&worker='.$user->id.'&created_date='.$daterange;

    // Last week
    $ch = curl_init($config->url.$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
    curl_setopt($ch, CURLOPT_COOKIE, 'wrms3_auth='.$config->secret);
    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result);
    if (empty($response->success) || $response->response->results_count != 1) {
        return false;
    }
    $totalhours = $response->response->results[0]->hours_sum;

    return !empty($totalhours) ? round($totalhours, 2) : 0;
}
