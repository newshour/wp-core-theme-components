<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

use ReflectionClass;
use SplObjectStorage;
use WP_CLI;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use NewsHour\WPCoreThemeComponents\Commands\Command;
use NewsHour\WPCoreThemeComponents\Commands\ContainerCommandResolver;
use NewsHour\WPCoreThemeComponents\Containers\ContainerFactory;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
/**
 * Provides a service for adding new manager classes. This service is run
 * in functions.php.
 *
 * @final
 */
final class ManagerService
{
    // SplObjectStorage
    private SplObjectStorage $managers;

    // Request object.
    private Request $request;

    // The singleton
    private static $instance;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->managers = new SplObjectStorage();
        $this->request = $request;
    }

    /**
     * @return ManagerService
     */
    public static function instance()
    {
        if (self::$instance == null) {
            // Make sure WP_HOME exists.
            if (!defined('WP_HOME') || empty(WP_HOME)) {
                trigger_error('The constant WP_HOME is not defined.', E_USER_ERROR);
            }

            self::$instance = new ManagerService(RequestFactory::get());

            // Add the bootstrap manager.
            self::$instance->add(Bootstrap::class);
        }

        return self::$instance;
    }

    /**
     * Add a WordpressManager to the pipeline. If the manager implements ContainerAwareInterface,
     * the container will also be set.
     *
     * @param  string $className
     * @return ManagerService
     */
    public function add($className, array $args = [])
    {
        $reflector = new ReflectionClass((string)$className);

        if (!$reflector->implementsInterface(WordpressManager::class)) {
            trigger_error((string)$className . ' is not a WordpressManager.', E_USER_WARNING);
            return $this;
        }

        if ($reflector->hasMethod('__construct')) {
            array_unshift($args, $this->request);
            $this->managers->attach($reflector->newInstanceArgs($args));
            return $this;
        }

        if (count($args) > 0) {
            trigger_error(
                (string)$className . ' must declare a constructor first to pass arguments.',
                E_USER_WARNING
            );
            return $this;
        }

        $this->managers->attach($reflector->newInstance());

        return $this;
    }

    /**
     * Add an array of managers.
     *
     * @param  array $classNameList
     * @return ManagerService
     */
    public function addAll(array $classNameList)
    {
        foreach ($classNameList as $className) {
            if (is_string($className)) {
                $this->add($className);
            } elseif (is_array($className)) {
                $_className = array_shift($className);
                $args = count($className) > 0 ? $className : [];
                $this->add($_className, $args);
            }
        }

        return $this;
    }

    /**
     * Add a WP CLI command.
     *
     * @param  Command $command
     * @return ManagerService
     */
    public function addCommand($className, $attachToAction = 'init')
    {
        $reflector = new ReflectionClass((string)$className);

        if (!$reflector->implementsInterface(Command::class)) {
            trigger_error((string)$className . ' is not a Command.', E_USER_WARNING);
            return $this;
        }

        if ($reflector->implementsInterface(ContainerAwareInterface::class)) {
            add_action($attachToAction, function () use ($className) {
                $resolver = new ContainerCommandResolver(ContainerFactory::get());
                $command = $resolver->getCommand($className);
                WP_CLI::add_command((string)$command, $command);
            });

            return $this;
        }

        add_action($attachToAction, function () use ($reflector) {
            WP_CLI::add_command((string)$command, $reflector->newInstance());
        });

        return $this;
    }

    /**
     * Add an array of Commands.
     *
     * @param  array $commands
     * @return ManagerService
     */
    public function addAllCommands(array $classNames)
    {
        if (count($classNames) > 0) {
            foreach ($classNames as $className) {
                $this->addCommand($className);
            }
        }

        return $this;
    }

    /**
     * Run managers.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->managers as $manager) {
            $manager->run();
        }
    }
}
