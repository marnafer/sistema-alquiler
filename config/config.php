<?php

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'dev_secret_123');
define('JWT_EXPIRATION', 3600);

define('APP_ENV', 'development');

// DB
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_alquiler');
define('DB_USER', 'root');
define('DB_PASS', '');

// Config de errores
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
}