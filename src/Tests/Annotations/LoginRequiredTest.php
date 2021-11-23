<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Annotations;

use ReflectionMethod;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

use PHPUnit\Framework\TestCase;

use NewsHour\WPCoreThemeComponents\Annotations\LoginRequired;
use NewsHour\WPCoreThemeComponents\Tests\DummyController;

class LoginRequiredTest extends TestCase
{
    /**
     * @return void
     */
    public function setup(): void
    {
        require_once dirname(__DIR__) . '/mock_wp_functions.php';

        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * @return void
     */
    public function testLoginRequired(): void
    {
        $reader = new AnnotationReader();
        $loginRequired = $reader->getMethodAnnotation(
            new ReflectionMethod(DummyController::class, 'loginRequiredMethod'),
            LoginRequired::class
        );

        $this->assertIsObject($loginRequired);
        $this->assertFalse($loginRequired->validateUser());
    }
}
