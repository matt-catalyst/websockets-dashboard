<?php

function dashboard_return_config($section = null) {
    static $config;

    if (is_null($config)) {
        $config = parse_ini_file(dirname(__DIR__).'/config.ini', true);
        if (!is_array($config)) {
            $config = array();
        }
    }

    if (is_null($section)) {
        return $config;
    }

    if (isset($config[$section])) {
        return $config[$section];
    }

    return array();
}


function dashboard_push_data($plugin, $data, $multiple = false) {
    static $host;

    if (is_null($host)) {
        $config = dashboard_return_config('general');
        $host = $config['host'].':'.$config['port'];
    }

    if ($multiple) {
        $d = 'multiple=1';
        foreach ($data as $item) {
            $json = json_encode($item);
            $d .= "&data=".urlencode($json);
        }
    } else {
        $json = json_encode($data);
        $d = 'data='.urlencode($json);
    }

    $ch = curl_init("{$host}/update/{$plugin}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
    curl_exec($ch);
    curl_close($ch);
}
