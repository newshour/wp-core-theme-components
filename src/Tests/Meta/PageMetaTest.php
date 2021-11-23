<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\PageMeta;

class PageMetaTest extends TestCase
{
    private PageMeta $pageMeta;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->pageMeta = new PageMeta();
    }

    /**
     * @return void
     */
    public function testRender(): void
    {
        $this->assertIsString(
            $this->pageMeta->render()
        );
    }

    /**
     * @return void
     */
    public function testKeywords(): void
    {
        $this->pageMeta->setKeywords('word_1, word_2, word_3');

        $this->assertIsArray(
            $this->pageMeta->getKeywords()
        );
    }
}
