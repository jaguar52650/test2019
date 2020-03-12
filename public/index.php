<?
error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8");

define('DB_HOST', 'localhost');
define('DB_NAME', 'v8');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8');

set_include_path(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
spl_autoload_extensions('.php');
spl_autoload_register();


Router::getInstance()->route();


