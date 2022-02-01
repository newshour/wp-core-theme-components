<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents;

use Exception;
use InvalidArgumentException;
use Throwable;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use NewsHour\WPCoreThemeComponents\DependencyInjection\Compiler\ManagerPass;
use NewsHour\WPCoreThemeComponents\DependencyInjection\Compiler\WpCommandPass;
use NewsHour\WPCoreThemeComponents\DependencyInjection\Compiler\WpScreenPass;
use NewsHour\WPCoreThemeComponents\Factories\FileLoaderFactory;

/**
 * Extends the Symfony Kernel for use with Wordpress and the Core Theme.
 */
final class CoreThemeKernel extends Kernel
{
    /**
     * Constructor is private. Use create() to build new kernel instances.
     *
     * @param string $environment
     * @param boolean $debug
     */
    private function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
    }

    /**
     * Creates a new, booted CoreThemeKernel instance.
     *
     * @param string $environment
     * @param boolean $debug
     * @return self
     */
    public static function create(string $environment, bool $debug): self
    {
        $kernel = new CoreThemeKernel($environment, $debug);
        $kernel->boot();
        restore_error_handler();
        return $kernel;
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @return void
     */
    public function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new RegisterListenersPass());
        $containerBuilder->addCompilerPass(new ManagerPass());
        $containerBuilder->addCompilerPass(new WpCommandPass());
        $containerBuilder->addCompilerPass(new WpScreenPass());
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = parent::getContainerBuilder();

        // Apply any container filters.
        if (function_exists('apply_filters')) {
            $containerBuilder = apply_filters('core_theme_container', $containerBuilder);
        }

        return $containerBuilder;
    }

    /**
     * @return iterable
     */
    public function registerBundles(): iterable
    {
        $contents = require $this->getBundlesPath();
        foreach ($contents as $class => $envs) {
            if (isset($envs[$this->environment]) || isset($envs['all'])) {
                yield new $class();
            }
        }
    }

    /**
     * Container configurations are set in src/Resources/config/services.php
     * and loaded here.
     *
     * @param LoaderInterface $loader
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $container->prependExtensionConfig('framework', [
                'translator' => ['enabled' => false]
            ]);

            if (!$container->hasDefinition('kernel')) {
                $container->register('kernel', self::class)
                    ->addTag('controller.service_arguments')
                    ->setAutoconfigured(true)
                    ->setSynthetic(true)
                    ->setPublic(true)
                ;
            }

            // Per https://symfony.com/doc/current/performance.html#dump-the-service-container-into-a-single-file
            // Always set this since PHP 7.4 is the lowest version supported.
            $container->setParameter('container.dumper.inline_factories', true);
            $container->addObjectResource($this);
            $container->fileExists($this->getBundlesPath());

            $fileTypes = ['yaml', 'xml', 'php'];

            foreach ($fileTypes as $type) {
                $this->import(
                    $type,
                    [
                        'services.' . $type,
                        'services_' . $this->getEnvironment() . '.' . $type
                    ],
                    $container
                );
            }

            $themeExt = new DependencyInjection\ThemeExtension();
            $container->registerExtension($themeExt);
            $container->loadFromExtension($themeExt->getAlias());

            foreach ($fileTypes as $type) {
                $this->import(
                    $type,
                    [
                        '{packages}/*.' . $type,
                        '{packages}/' . $this->getEnvironment() . '/*.' . $type
                    ],
                    $container
                );
            }

            $container->setAlias(self::class, 'kernel')->setPublic(true);
        });
    }

    /**
     * @param Request $request
     * @param int $type
     * @param boolean $catch
     * @return Response
     */
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        try {
            $resolver = new ContainerControllerResolver($this->getContainer());
            $controller = $resolver->getController($request);

            if (!is_callable($controller)) {
                throw new Exception(
                    sprintf(
                        'Error: <b>%s</b> could not be resolved in the container.',
                        $request->attributes->get('_controller')
                    )
                );
            }

            $args = (new ArgumentResolver())->getArguments($request, $controller);
            return call_user_func_array($controller, $args);
        } catch (Exception $e) {
            if (!$catch) {
                throw ($e);
            }

            $throwable = $e;
        } catch (Throwable $t) {
            if (!$catch) {
                throw ($t);
            }

            $throwable = $t;
        }

        $event = new ExceptionEvent($this, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);
        $this->getEventDispatcher()->dispatch($event, KernelEvents::EXCEPTION);

        if ($event->hasResponse()) {
            $response = $event->getResponse();
            $response->send();
            $this->terminate($request, $response);
            $this->shutdown();
            exit;
        }
    }

    /**
     * @return object
     */
    public function getEventDispatcher(): object
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    /**
     * @return string
     */
    public function getBundlesPath(): string
    {
        return $this->getConfigDir() . '/bundles.php';
    }

    /**
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getProjectDir(): string
    {
        if (!defined('BASE_DIR')) {
            throw new InvalidArgumentException(
                'The constant `BASE_DIR` is missing and should be set in `config/application.php`'
            );
        }

        if (empty($this->projectDir)) {
            $this->projectDir = rtrim(BASE_DIR, '/');
        }

        return $this->projectDir;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getConfigDir(): string
    {
        if (empty($this->configDir)) {
            if (!is_dir($configDir = $this->getProjectDir() . '/config')) {
                throw new InvalidArgumentException(
                    sprintf('Unable to find your config dir. Looked in "%s".', $configDir)
                );
            }

            $this->configDir = $configDir;
        }

        return $this->configDir;
    }

    /**
     * Gets the charset of the application.
     *
     * @return string
     */
    public function getCharset(): string
    {
        if (function_exists('get_bloginfo')) {
            return get_bloginfo('charset');
        }

        return 'UTF-8';
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        if (function_exists('get_locale')) {
            return get_locale();
        }

        return '';
    }

    /**
     * @param string $type
     * @param array $paths
     * @param ContainerBuilder $container
     * @return void
     */
    private function import($type, $paths, $container): void
    {
        $locator = new FileLocator($this->getConfigDir());

        // Heads up: each import needs a new FileLoader instance.
        foreach ($paths as $path) {
            $loader = FileLoaderFactory::create($type, $container, $locator);
            $loader->import($path, $type, 'not_found');
        }
    }
}
