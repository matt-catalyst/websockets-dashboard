<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

// Last dataset hash
$lasthash = '';


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
            $states[$name] = STATE_CRITICAL;
        } else {
            $states[$name] = $types[$state];
        }

        $codes[$name] = $group;
    }

    $newhash = serialize($codes);

    if ($newhash != $lasthash) {
        print "Update sent\n";
        dashboard_push_data('nagios', array('lastchange' => time(), 'groups' => $codes, 'states' => $states));
    }

    $lasthash = $newhash;

    sleep(60);
}
