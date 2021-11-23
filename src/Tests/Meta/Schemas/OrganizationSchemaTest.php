<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\ImageSchema;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\OrganizationSchema;

class OrganizationSchemaTest extends TestCase
{
    private OrganizationSchema $schema;
    private ImageSchema $logo;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new OrganizationSchema();
        $this->logo = new ImageSchema();
        $this->logo->setUrl('http://some/image/url.png');
    }

    /**
     * @return void
     */
    public function testCreateFromBlogInfo(): void
    {
        $obj = OrganizationSchema::createFromBlogInfo();

        $this->assertInstanceOf(
            OrganizationSchema::class,
            $obj
        );
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $logo = new ImageSchema();
        $logo->setUrl('http://some/image/url.png');

        $this->schema->setLogo($logo);
        $this->schema->setName('Test Organization');
        $this->schema->setUrl('http://some-organization-url.localhost');
        $this->schema->setIsPublisher(true);
        $this->schema->setIsNewsMediaOrg(true);

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }
}
