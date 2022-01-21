<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Controllers;

use Exception;
use ReflectionClass;
use ReflectionException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PsrCachedReader;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use NewsHour\WPCoreThemeComponents\Annotations\HttpMethods;
use NewsHour\WPCoreThemeComponents\Annotations\LoginRequired;
use NewsHour\WPCoreThemeComponents\CoreThemeKernel;
use NewsHour\WPCoreThemeComponents\Events\AnnotationEvent;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
use NewsHour\WPCoreThemeComponents\Utilities;

/**
 * Loads controller classes from the Wordpress "template" files. e.g. single.php,
 * single-some-post-type.php, page.php, etc.
 *
 * @final
 */
final class FrontController
{
    /**
     * Loads a controller from the DI container and invokes the passed method name ($method).
     *
     * @param string $controllerClass
     * @param string $method
     * @param array $params Optional method arguments passed as key/value.
     * @return Controller
     */
    public static function run(string $controllerClass, string $method, array $params = [])
    {
        $kernel = CoreThemeKernel::create(WP_ENV, WP_DEBUG);
        $request = RequestFactory::get();

        try {
            $reflectedClass = new ReflectionClass($controllerClass);

            if (!$reflectedClass->isInstantiable() || !$reflectedClass->isSubclassOf(Controller::class)) {
                throw new ReflectionException(
                    "Error: <b>{$controllerClass}</b> is not a valid Controller."
                );
            }

            if (!$reflectedClass->hasMethod($method)) {
                throw new ReflectionException(
                    "A method named <b>{$method}</b> could not be found in <i>{$controllerClass}</i>."
                );
            }

            // Start processing annotations.
            AnnotationRegistry::registerLoader('class_exists');
            $debug = defined('WP_DEBUG') ? WP_DEBUG : false;

            $reader = new PsrCachedReader(
                new AnnotationReader(),
                new PhpArrayAdapter(
                    dirname(__DIR__, 2) . '/cache/app/annotations.php',
                    new FilesystemAdapter()
                ),
                $debug
            );

            // LoginRequired annotations.
            $classLoginRequired = $reader->getClassAnnotation(
                $reflectedClass,
                LoginRequired::class
            );

            $methodLoginRequired = $reader->getMethodAnnotation(
                $reflectedClass->getMethod($method),
                LoginRequired::class
            );

            // Check class annotation, then method.
            if ($classLoginRequired !== null && !$classLoginRequired->validateUser()) {
                Utilities::exitOnError('403 Access Forbidden', 'Error', 403, $kernel, $request);
            } elseif ($methodLoginRequired !== null && !$methodLoginRequired->validateUser()) {
                Utilities::exitOnError('403 Access Forbidden', 'Error', 405, $kernel, $request);
            }

            // Set the controller args.
            $request->attributes->set(
                '_controller',
                [$controllerClass, $method]
            );

            // Add any method arguments.
            if (count($params) > 0) {
                $request->attributes->add($params);
            }

            // HttpMethods annotation.
            $httpMethods = $reader->getMethodAnnotation(
                $reflectedClass->getMethod($method),
                HttpMethods::class
            );

            // Default are "safe" HTTP methods. Allow only if annotation value defines unsafe methods it.
            $allowed = ($httpMethods !== null) ? $httpMethods->validateMethods($request) : $request->isMethodSafe();

            if (!$allowed) {
                Utilities::exitOnError('405 Method Not Allowed', 'Error', 405, $kernel, $request);
            }

            // Load the controller from the container.
            $response = $kernel->handle($request, CoreThemeKernel::MAIN_REQUEST, false);

            if ($response instanceof Response) {
                // Apply any post controller action filters.
                $response = apply_filters('core_theme_response', $response);

                // Send the response.
                $response->send();

                // We're all done. Wordpress will run its `shutdown` action on exit.
                $kernel->terminate($request, $response);
                $kernel->shutdown();
                exit;
            }

            $throwable = new Exception(
                sprintf(
                    '<b>%s</b> did not return a Response object. Did you forget the <i>return</i> statement?',
                    $controllerClass . '::' . $method
                )
            );
        } catch (ReflectionException $re) {
            $throwable = $re;
        } catch (Exception $e) {
            $throwable = $e;
        }

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);
        $kernel->getEventDispatcher()->dispatch($event, KernelEvents::EXCEPTION);

        if ($event->hasResponse()) {
            $response = $event->getResponse();
            $response->send();
            $kernel->terminate($request, $response);
            $kernel->shutdown();
            exit;
        }

        Utilities::exitOnError($throwable->getMessage(), 'Error', 500);
    }
}
