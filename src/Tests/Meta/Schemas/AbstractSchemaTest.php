<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\ImageSchema;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\OrganizationSchema;

class AbstractSchemaTest extends TestCase
{
    private DummySchema $schema;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new DummySchema();
    }

    /**
     * @return void
     */
    public function testIsEmpty(): void
    {
        $this->schema->addIdentifier('@id', '#id');

        $isEmpty = $this->schema->isEmpty();

        $this->assertIsBool($isEmpty);
        $this->assertTrue($isEmpty);
    }

    /**
     * @return void
     */
    public function testIsNotEmpty(): void
    {
        $orgSchema = new OrganizationSchema();
        $orgSchema->setName('Test Org, Inc');

        $this->schema->setOrganization($orgSchema);

        $isEmpty = $this->schema->isEmpty();

        $this->assertIsBool($isEmpty);
        $this->assertFalse($isEmpty);
    }

    /**
     * @return void
     */
    public function testSameAsArray(): void
    {
        $this->schema->addSameAs([
            'http://url.localhost',
            'https://url.localhost',
            'https://url-copy.localhost',
            'https://url-copy.localhost'
        ]);

        $sameAs = $this->schema->getSameAs();

        $this->assertIsArray($sameAs);
        $this->assertCount(3, $sameAs);
    }

    /**
     * @return void
     */
    public function testImageArray(): void
    {
        $this->schema->addImage([
            'http://some/img/url.png',
            'http://some-other/img/url.png',
            'http://some-copy/img/url.png',
            'http://some-copy/img/url.png',
        ]);

        $images = $this->schema->getImages();

        $this->assertIsArray($images);
        $this->assertCount(3, $images);
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->schema->setPublisher('Test Publisher')
            ->setDatePublished(time(), 'UTC')
            ->setDateModified(time(), 'UTC');

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }
}
