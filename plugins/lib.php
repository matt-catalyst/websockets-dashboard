<?php


function dashboard_push_data($plugin, $data) {
    $json = json_encode($data);
    $d = 'data='.urlencode($json);

    $ch = curl_init("http://127.0.0.1:8888/update/{$plugin}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
    curl_exec($ch);
    curl_close($ch);
}
