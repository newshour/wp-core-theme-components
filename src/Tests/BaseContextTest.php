<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests;

use NewsHour\WPCoreThemeComponents\Contexts\BaseContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BaseContextTest extends TestCase {

    public function testToString(): void {

        $context = new BaseContext(Request::createFromGlobals());
        $this->assertIsString(strval($context));

    }

    public function testToArray(): void {

        $context = new BaseContext(Request::createFromGlobals());
        $this->assertIsArray($context->toArray());

    }

    public function testCount(): void {

        $context = new BaseContext(Request::createFromGlobals(), ['foo' => 'bar']);
        $this->assertGreaterThan(0, count($context));

    }

    public function testGetRequest(): void {

        $context = new BaseContext(Request::createFromGlobals());
        $this->assertInstanceOf(Request::class, $context->getRequest());

    }

}