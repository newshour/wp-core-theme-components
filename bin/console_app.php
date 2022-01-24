<?php

use NewsHour\WPCoreThemeComponents\CoreThemeKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\ErrorHandler\Debug;

if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the ' . PHP_SAPI . ' SAPI' . PHP_EOL;
}

$baseDir = '';

for ($x = 2; $x < 6; $x++) {
    $checkDir = dirname(__FILE__, $x);
    if (file_exists(dirname(__FILE__, $x) . '/vendor/autoload.php')) {
        $baseDir = $checkDir;
        break;
    }
}

if (empty($baseDir)) {
    echo 'vendor/autoload.php was not found.' . PHP_EOL;
    exit(1);
}

include_once $baseDir . '/vendor/autoload.php';

$configDir = $baseDir . '/config';

if (!file_exists($configDir . '/application.php')) {
    echo 'config/application.php not found. Looked in ' . $dir . '.' . PHP_EOL;
    exit(1);
}

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], null, true);

if (null !== $env) {
    putenv('WP_ENV=' . $_SERVER['WP_ENV'] = $_ENV['WP_ENV'] = $env);
}

if ($input->hasParameterOption('--no-debug', true)) {
    putenv('WP_DEBUG=' . $_SERVER['WP_DEBUG'] = $_ENV['WP_DEBUG'] = '0');
}

// WP_DEBUG will be defined when application.php is loaded.
include_once $configDir . '/application.php';

putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $_SERVER['WP_ENV']);
putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = WP_DEBUG);

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    if (class_exists(Debug::class)) {
        Debug::enable();
    }
}

$kernel = CoreThemeKernel::create($_SERVER['WP_ENV'], $_SERVER['APP_DEBUG']);
$application = new Application($kernel);
$application->run($input);
