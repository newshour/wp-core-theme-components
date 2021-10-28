<?php
/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Controllers;

use Exception;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

use NewsHour\WPCoreThemeComponents\Annotations\HttpMethods;
use NewsHour\WPCoreThemeComponents\Contexts\Context;
use NewsHour\WPCoreThemeComponents\Contexts\ContextFactory;

/**
 * Loads controller classes from the Wordpress "template" files. e.g. single.php,
 * single-some-post-type.php, page.php, etc.
 *
 * @final
 */
final class FrontController {

    /**
     * Loads a controller and invokes the method. An optional context object can
     * be passed. If one is not, the default context defined in ContextFactory
     * is loaded.
     *
     * @param  string  $className
     * @param string $method
     * @param  Context $context
     * @return Controller
     */
    public static function run(string $controllerClass, string $method, Context $context = null) {

        try {

            $reflectedClass = new ReflectionClass($controllerClass);

            if (!$reflectedClass->isInstantiable() || !$reflectedClass->isSubclassOf(Controller::class)) {
                trigger_error(
                    sprintf(
                        'Error: <b>%s</b> is not a valid Controller.',
                        $controllerClass
                    ),
                    E_USER_ERROR
                );
            }

            if (!$reflectedClass->hasMethod($method)) {
                trigger_error(
                    sprintf(
                        'A method named <b>%s</b> could not be found in <i>%s</i>.',
                        $method,
                        $controllerClass
                    ),
                    E_USER_ERROR
                );
            }

            if ($context === null) {
                $context = ContextFactory::default();
            }

            // Start processing annotations.
            AnnotationRegistry::registerLoader('class_exists');
            $reader = new AnnotationReader();

            // HttpMethods annotation.
            $httpMethods = $reader->getMethodAnnotation(
                $reflectedClass->getMethod($method),
                HttpMethods::class
            );

            $request = $context->getRequest();

            // Default are "safe" HTTP methods. Allow only if annotation value defines unsafe methods it.
            if (!$request->isMethodSafe() || ($httpMethods !== null && !$httpMethods->validateMethods($context->getRequest()))) {
                \wp_die(
                    '405 Method Not Allowed',
                    'Error',
                    ['response' => 405]
                );
            }

            $instance = $reflectedClass->newInstance($context);

            return (new ReflectionMethod($controllerClass, $method))->invoke($instance);

        } catch (ReflectionException $re) {

            trigger_error(
                sprintf('%s [stack trace] %s', $re->getMessage(), $re->getTraceAsString()),
                E_USER_ERROR
            );

        } catch (Exception $e) {

            trigger_error(
                sprintf('%s [stack trace] %s', $e->getMessage(), $e->getTraceAsString()),
                E_USER_ERROR
            );

        }

    }

}
