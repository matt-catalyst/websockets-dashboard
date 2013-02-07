<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

// Last dataset hash
$lasthash = '';
$lastcodes = array();
$laststates = array();

define('STATE_OK',          'OK');
define('STATE_WARNING',     'Warning');
define('STATE_UNKNOWN',     'Unknown');
define('STATE_CRITICAL',    'Critical');

while (1) {

    print "API call\n";

    $codes = array();
    $states = array();
    foreach ($config->groups as $name => $url) {

        $data = array();
        exec("curl -s --insecure $url | grep serviceTotalsO -A 3 | awk -F'[>|<]' '{ print $3 }'", $data);

        $group = array();
        $state = 0;

        $types = array(STATE_OK, STATE_WARNING, STATE_UNKNOWN, STATE_CRITICAL);

        foreach ($data as $i => $row) {
            $group[$types[$i]] = $row;

            if ($row) {
                $state = max($state, $i);
            }
        }

        // If no data supplied, critical
        if (!count($data)) {
            $state = STATE_CRITICAL;
        } else {
            $state = $types[$state];
        }

        // Check when state last changed
        if (!in_array($name, array_keys($laststates)) || $laststates[$name][0] !== $state) {
            $timechanged = time();
        } else {
            $timechanged = $laststates[$name][1];
        }

        $states[$name] = array($state, $timechanged);
        $codes[$name] = $group;
    }

    $newhash = md5(serialize($codes) . serialize($states));

    if ($newhash != $lasthash) {
        print "Update sent\n";
        dashboard_push_data('nagios', array('lastchange' => time(), 'groups' => $codes, 'states' => $states));
    }

    $lasthash = $newhash;
    $lastcodes = $codes;
    $laststates = $states;

    sleep(30);
}
