<?php
define('SITE_URL'        , '/twobus-phone/'); //URI base, e.g. http://localhost/ Relative paths OK
define('DOC_ROOT'        , dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('CACHE_DIR'       , DOC_ROOT . 'cache' . DIRECTORY_SEPARATOR);
define('CONFIG_DIR'      , DOC_ROOT . 'config' . DIRECTORY_SEPARATOR);

define('MAX_DEPARTURES'  , 4);
define('MINUTE_PRECISION', 1);
define('FUTURE_MINUTES'  , 90);

define('API_BASE'        , 'http://api.pugetsound.onebusaway.org/api/');
define('API_KEY'         , 'TEST');

define('DB_IMPL'         , 'database-sample');
define('DB_HOST'         , 'localhost');
define('DB_USER'         , 'my_user');
define('DB_PASS'         , 'my_password');
define('DB_DATABASE'     , 'my_db');