<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\FacebookMeta;
use NewsHour\WPCoreThemeComponents\Tests\DummyCorePost;

class FacebookMetaTest extends TestCase
{
    private FacebookMeta $facebookMeta;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $meta = new FacebookMeta();
        $meta->setAppId(['0123456789']);
        $meta->setTitle('A Test Title');
        $meta->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $meta->setImageUrl('http://some/image/url.png');
        $meta->setImageHeight(150);
        $meta->setImageWidth(150);
        $this->facebookMeta = $meta;
    }

    /**
     * @return void
     */
    public function testModifiedOn(): void
    {
        $this->facebookMeta->setModifiedOn(time(), 'UTC');

        $this->assertInstanceOf(
            Carbon::class,
            $this->facebookMeta->getModifiedOn()
        );
    }

    /**
     * @return void
     */
    public function testPublishedOn(): void
    {
        $this->facebookMeta->setPublishedOn(time(), 'UTC');

        $this->assertInstanceOf(
            Carbon::class,
            $this->facebookMeta->getPublishedOn()
        );
    }

    /**
     * @return void
     */
    public function testCreateFromCorePost(): void
    {
        $created = FacebookMeta::createFromCorePost(new DummyCorePost());
        $this->assertInstanceOf(FacebookMeta::class, $created);
    }

    /**
     * @return void
     */
    public function testRender(): void
    {
        $this->assertIsString(
            $this->facebookMeta->render()
        );
    }

    /**
     * @return void
     */
    public function testTags(): void
    {
        $this->facebookMeta->setTags('tag_1, tag_2, tag_3');

        $this->assertIsArray(
            $this->facebookMeta->getTags()
        );
    }
}
