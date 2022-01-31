<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

use ReflectionClass;
use SplObjectStorage;
use Throwable;
use WP_CLI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use NewsHour\WPCoreThemeComponents\Commands\Command;
use NewsHour\WPCoreThemeComponents\Containers\ContainerFactory;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
use NewsHour\WPCoreThemeComponents\KernelUtilities;

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

    // The container.
    private ContainerInterface $container;

    // The singleton
    private static $instance;

    /**
     * The constructor is private, use instance() instead.
     *
     * @param Request $request
     * @param ContainerInterface $container
     */
    private function __construct(Request $request, ContainerInterface $container)
    {
        $this->managers = new SplObjectStorage();
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @return ManagerService
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new ManagerService(RequestFactory::get(), ContainerFactory::get());

            // Always add the bootstrap manager.
            self::$instance->add(Bootstrap::class);
        }

        return self::$instance;
    }

    /**
     * Add a WordpressManager to the pipeline.
     *
     * @param  string $className
     * @return self
     */
    public function add(string $className): self
    {
        if (!is_string($className)) {
            $this->exitOnError(
                $this->logError('className argument must be a class string.')
            );
        }

        $reflector = new ReflectionClass($className);

        if (!$reflector->implementsInterface(WordpressManager::class)) {
            $this->exitOnError(
                $this->logError($className . ' is not a WordpressManager.')
            );
        }

        try {
            if (!$this->container->has($className)) {
                $this->exitOnError(
                    $this->logError(
                        $className . ' is not registered as a service or was not tagged with `core_theme.manager`.'
                    )
                );
            }

            $this->managers->attach($this->container->get($className));
        } catch (Throwable $e) {
            $this->exitOnError(
                $this->logError($e->getMessage())
            );
        }

        return $this;
    }

    /**
     * Add an array of managers.
     *
     * @param  array $classNameList
     * @return self
     */
    public function addAll(array $classNameList): self
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
     * @return self
     */
    public function addCommand(string $className, $attachToAction = 'init'): self
    {
        $reflector = new ReflectionClass($className);

        if (!$reflector->implementsInterface(Command::class)) {
            $this->exitOnError(
                $this->logError($className . ' does not implement Command.')
            );
        }

        try {
            if (!$this->container->has($className)) {
                $this->exitOnError(
                    $this->logError(
                        $className . ' is not registered as a service or was not tagged with `wp.command`.'
                    )
                );
            }

            $command = $this->container->get($className);
            add_action($attachToAction, fn () => WP_CLI::add_command((string)$command, $command));
        } catch (Throwable $e) {
            $this->exitOnError(
                $this->logError($e->getMessage())
            );
        }

        return $this;
    }

    /**
     * Add an array of Commands.
     *
     * @param  array $commands
     * @return self
     */
    public function addAllCommands(array $classNames): self
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
    public function run(): void
    {
        foreach ($this->managers as $manager) {
            $manager->run();
        }
    }

    /**
     * @param string $message
     * @return string
     */
    private function logError(string $message): string
    {
        if ($this->container->has('logger')) {
            $logger = $this->container->get('logger');
            $logger->error($message);
            return $message;
        }

        error_log($message);
        return $message;
    }

    /**
     * @param string $message
     * @param string $title
     * @param integer $statusCode
     * @return void
     */
    private function exitOnError(string $message, string $title = 'Error', int $statusCode = 500)
    {
        if ($this->container->has('kernel')) {
            $kernel = $this->container->get('kernel');
            KernelUtilities::create($kernel, $this->request)->exitOnError($message, $title, $statusCode);
        }

        if (function_exists('wp_die')) {
            wp_die($message, $title, ['response' => $statusCode]);
            exit;
        }
    }
}
