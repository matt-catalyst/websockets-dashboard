<?php
require_once(dirname(__FILE__).'/../lib.php');


if (empty($argv[1])) {
    die ("Usage: $argv[0] URL\n\n");
}

$url = $argv[1];

// Data to send
dashboard_push_data('iframe', $url, false);

