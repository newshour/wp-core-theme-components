<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\TwitterMeta;
use NewsHour\WPCoreThemeComponents\Tests\DummyCorePost;

class TwitterMetaTest extends TestCase
{
    private TwitterMeta $twitterMeta;

    /**
     * @return void
     */
    public function setUp(): void
    {
        require_once dirname(__DIR__) . '/mock_wp_functions.php';

        $meta = new TwitterMeta();
        $meta->setSite('twitter');
        $meta->setTitle('Test Title');
        $meta->setCard('summary');
        $meta->setImageUrl('http://some/image/url.png');
        $meta->setDoNotTrack(true);
        $this->twitterMeta = $meta;
    }

    public function testCreateFromPost(): void
    {
        $created = TwitterMeta::createFromCorePost(new DummyCorePost());
        $this->assertInstanceOf(TwitterMeta::class, $created);
    }

    /**
     * @return void
     */
    public function testRender(): void
    {
        $this->assertIsString($this->twitterMeta->render());
    }

    public function testGetSite(): void
    {
        $this->assertStringStartsWith('@', $this->twitterMeta->getSite());
    }
}
