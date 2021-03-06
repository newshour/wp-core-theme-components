<?php

/**
 * @version 1.0.0
 */

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\ErrorHandler\Debug;
use NewsHour\WPCoreThemeComponents\Console\CoreThemeApplication;
use NewsHour\WPCoreThemeComponents\CoreThemeKernel;

if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the ' . PHP_SAPI . ' SAPI' . PHP_EOL;
}

$baseDir = '';
$binDir = isset($GLOBALS['_composer_bin_dir']) ? $GLOBALS['_composer_bin_dir'] : dirname(__FILE__);

for ($x = 2; $x < 6; $x++) {
    $checkDir = dirname($binDir, $x);
    if (file_exists($checkDir . '/vendor/autoload.php')) {
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
    echo 'config/application.php not found. Looked in ' . $configDir . '.' . PHP_EOL;
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

// Load the Wordpress environment if requested.
if ($input->hasParameterOption(['--with-wordpress'], true)) {
    if (!file_exists(ABSPATH . 'wp-load.php')) {
        echo 'wp-load.php not found. Looked in ' . ABSPATH . '.' . PHP_EOL;
        exit(1);
    }

    define('WP_USE_THEMES', false);
    global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header, $table_prefix;
    include_once ABSPATH . 'wp-load.php';
}

// Load kernel and console app.
$kernel = CoreThemeKernel::create($_SERVER['WP_ENV'], $_SERVER['APP_DEBUG']);
$application = new CoreThemeApplication($kernel);
$application->run($input);
