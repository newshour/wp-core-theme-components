<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Controllers;

use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;
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
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
use NewsHour\WPCoreThemeComponents\KernelUtilities;

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

        if (WP_DEBUG) {
            $request->attributes->set('showException', true);
        }

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

            // LoginRequired annotations. Check class annotation, then method.
            $loginRequiredObj = $reader->getClassAnnotation(
                $reflectedClass,
                LoginRequired::class
            );

            if ($loginRequiredObj == null) {
                $loginRequiredObj = $reader->getMethodAnnotation(
                    $reflectedClass->getMethod($method),
                    LoginRequired::class
                );
            }

            if ($loginRequiredObj !== null && !$loginRequiredObj->validateUser()) {
                if (!empty($loginRequiredObj->getNext())) {
                    wp_safe_redirect(
                        $loginRequiredObj->getNext()
                    );

                    exit;
                }

                $statusTextMsg = sprintf(
                    '%s %s',
                    $loginRequiredObj->getStatusCode(),
                    Response::$statusTexts[$loginRequiredObj->getStatusCode()] ?? 'unknown status'
                );

                KernelUtilities::create($kernel, $request)->exitOnError(
                    $statusTextMsg,
                    'Error',
                    $loginRequiredObj->getStatusCode()
                );
            }

            // Set the controller args.
            $request->attributes->set('_controller', [$controllerClass, $method]);

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
                KernelUtilities::create($kernel, $request)->exitOnError(
                    '405 Method Not Allowed',
                    'Error',
                    405
                );
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
        } catch (Throwable $t) {
            $throwable = $t;
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

        KernelUtilities::create($kernel, $request)->exitOnError(
            $throwable->getMessage(),
            'Error',
            500
        );
    }
}
