<?php

declare(strict_types=1);

/**
 * GymFlow — Front Controller Bootstrap
 */

// 1. Error reporting based on environment
ini_set('display_errors', '1');
error_reporting(E_ALL);

// 2. Autoloader
$baseDir = dirname(__DIR__);
require_once $baseDir . '/vendor/autoload.php';

use App\Services\Config;
use App\Services\Session;
use App\Services\Router;

// 3. Load Environment & Configuration
Config::load($baseDir);

$isDebug = (bool) Config::get('app.debug', false);
ini_set('display_errors', $isDebug ? '1' : '0');

// 4. Register Safe Exception/Error Logging Handlers
\App\Services\Logger::registerHandlers();

// 5. Set Timezone
date_default_timezone_set(Config::get('app.timezone', 'Asia/Kolkata'));

// 5. Initialize Session
Session::start();

// 6. Load Web Routes
require_once $baseDir . '/routes/web.php';

// 7. Dispatch HTTP Request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

Router::dispatch($method, $uri);
