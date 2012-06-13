<?php

define('RRD3_PATH', ''); // Directory containing RRD files
define('RRD3_RATE', 5);  // Refresh rate in seconds
define('RRD3_FILE_FILTER', '/staging|testing|eduforge|catalystdemo/'); // Regex match for files to exclude from the graphing loop
define('RRD3_DOMAIN', 'end-5m'); // y-axis for graphs. e.g. end-1y, end-2m, end-7d
