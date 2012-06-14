<?php

define('RRD3_PATH', ''); // Directory containing RRD files
define('RRD3_UPDATE_INTERVAL', 5);  // Number of seconds to wait between graph updates
define('RRD3_FILE_FILTER', '/staging|testing|eduforge|catalystdemo/'); // Regex match for files to exclude from the graphing loop
define('RRD3_DOMAIN', 'end-5m'); // y-axis for graphs. e.g. end-1y, end-2m, end-7d
