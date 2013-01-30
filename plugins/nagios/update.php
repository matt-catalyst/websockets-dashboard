<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(__FILE__).'/config.php');

// Last dataset hash
$lasthash = '';

while (1) {

    print "API call\n";

    $codes = array();
    $states = array();
    foreach ($config->groups as $name => $url) {

        $data = array();
        exec("curl -s --insecure $url | grep serviceTotalsO -A 3 | awk -F'[>|<]' '{ print $3 }'", $data);

        $group = array();
        $state = 0;

        $types = array('OK', 'Warning', 'Unknown', 'Critical');

        foreach ($data as $i => $row) {
            $group[$types[$i]] = $row;

            if ($row) {
                $state = max($state, $i);
            }
        }

        $codes[$name] = $group;
        $states[$name] = $types[$state];
    }

    $newhash = serialize($codes);

    if ($newhash != $lasthash) {
        print "Update sent\n";
        dashboard_push_data('nagios', array('lastchange' => time(), 'groups' => $codes, 'states' => $states));
    }

    $lasthash = $newhash;

    sleep(60);
}
