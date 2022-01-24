<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Commands\Composer;

use Exception;
use Composer\Script\Event;
use NewsHour\WPCoreThemeComponents\Commands\Command;

/**
 * Performs Composer `post-create-project-cmd` tasks.
 */
class PostCreateProjectCommand implements Command
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return self::class;
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function tasks(Event $event): void
    {
        // Creates a `bin/console` script in `<project_dir>/bin`. This allows for Symfony
        // CLI commands to be run consistently (e.g. `php bin/console cache:clear`).
        try {
            $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
            $binDir = dirname($vendorDir) . '/bin';

            if (!is_dir($binDir)) {
                echo "Creating bin/ directory." . PHP_EOL;
                @mkdir($binDir, 0755);
            }

            if (!file_exists($consoleApp = $binDir . '/console')) {
                echo "Creating bin/console file." . PHP_EOL;

                $contents = [
                    '#!/usr/bin/env php',
                    '<?php',
                    'include dirname(__FILE__, 2) . "/vendor/bin/console_app.php";'
                ];

                file_put_contents(
                    $consoleApp,
                    implode(PHP_EOL, $contents) . PHP_EOL,
                    LOCK_EX
                );

                chmod($consoleApp, 0644);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
