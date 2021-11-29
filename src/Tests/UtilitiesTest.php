<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Utilities;

class UtilitiesTest extends TestCase
{
    /**
     * @return void
     */
    public function testStringArrayUnique(): void
    {
        $data = ['item_1', 'item_2', 'item_3', 'item_3', ''];

        $this->assertCount(4, Utilities::stringArrayUnique($data));
    }

    /**
     * @return void
     */
    public function testStringArrayUniqueWithEmpty(): void
    {
        $data = ['item_1', 'item_2', 'item_3', 'item_3', '', false];

        $this->assertCount(3, Utilities::stringArrayUnique($data, true));
    }

    /**
     * @return void
     */
    public function testToCarbonObj(): void
    {
        $this->assertInstanceOf(
            Carbon::class,
            Utilities::toCarbonObj(time(), 'UTC')
        );
    }

    /**
     * @return void
     */
    public function testHasKey(): void
    {
        $this->assertTrue(
            Utilities::hasKey('foo', ['FoO' => 'bar', 'sna' => 'fu'])
        );
    }

    /**
     * @return void
     */
    public function testSplitter(): void
    {
        $this->assertIsArray(
            Utilities::splitter('Lorem ipsum dolor sit amet consectetur adipiscing elit')
        );
    }
}
