<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta;

use PHPUnit\Framework\TestCase;

use NewsHour\WPCoreThemeComponents\Components\Meta\TwitterMeta;

class TwitterMetaTest extends TestCase
{
    private TwitterMeta $twitterMeta;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $meta = new TwitterMeta();
        $meta->setSite('twitter');
        $meta->setTitle('Test Title');
        $meta->setCard('summary');
        $meta->setImageUrl('http://some/image/url.png');
        $meta->setDoNotTrack(true);
        $this->twitterMeta = $meta;
    }

    /**
     * @return void
     */
    public function testRender(): void
    {
        $this->assertIsString(
            $this->twitterMeta->render()
        );
    }
}
