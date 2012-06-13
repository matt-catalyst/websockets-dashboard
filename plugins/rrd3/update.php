<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib.php');


// Language strings for RRD file data.

$labels = array();

$labels['coursecount'] = array('name' => 'Course count',
                               'xlabel' => '',
                               'ylabel' => 'Courses');

$labels['dbsize'] = array('name' => 'Database Size',
                          'xlabel' => '',
                          'ylabel' => 'Size (MB)');

$labels['datarootsize'] = array('name' => 'Data-root Size',
                          'xlabel' => '',
                          'ylabel' => 'Size (MB)');

$labels['usercount'] = array('name' => 'User count',
                          'xlabel' => '',
                          'ylabel' => 'Users');

$labels['loghits'] = array('name' => 'Moodle log hits',
                           'xlabel' => '',
                           'ylabel' => 'Actions');

$labels['userlogins'] = array('name' => 'User logins',
                              'xlabel' => '',
                              'ylabel' => 'Logins');


if (!is_dir(RRD3_PATH)) {
    die("error opening RRD3_PATH: ".RRD3_PATH."\n");
}


while(1) {

    if ($dh = opendir(RRD3_PATH)) {
        while (($file = readdir($dh)) !== false) {
            if (!is_file(RRD3_PATH.$file)) {
                continue;
            }
            if (preg_match(RRD3_FILE_FILTER, $file)) {
                continue;
            }

            $site = strtr($file, array('_moodle' => '', '.rrd' => ''));
            echo "+ $file\n";
            $options = array("AVERAGE", "--start", RRD3_DOMAIN, "--end", "now");
            if ($data = rrd_fetch(RRD3_PATH.$file, $options)) {

                foreach($data['data'] as $name => $values) {
                    if (!isset($labels[$name])) {
                        continue;
                    }

                    array_walk($values, function(&$n) {
                            if (is_nan($n))
                            $n = 0.0;
                            });
                    $data['data'][$name] = array();
                    echo "  -$name\n";
                    foreach($values as $key => $value) {
                        $data['data'][$name][$key*1000] = $value;
                    }

                    $send = new stdClass();
                    $send->name = $labels[$name]['name'];
                    $send->data = $data['data'][$name];
                    $send->url = $site;
                    $send->ylabel = $labels[$name]['ylabel'];

                    // Data to send
                    dashboard_push_data('rrd3', $send, false);

                    sleep(RRD3_UPDATE_INTERVAL);
                }
            }
        }
    }
}
