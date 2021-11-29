<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use Timber\Image;

/**
 * Represents a subset of the ImageObject schema.
 */
class ImageSchema extends AbstractSchema
{
    public const SCHEMA_TYPE = 'ImageObject';

    /**
     * @param Image $image
     * @param string $size
     * @return ImageSchema
     */
    public static function createFromImageObj(Image $image, $size = 'large'): ImageSchema
    {
        $obj = new ImageSchema();
        $obj->setUrl($image->src($size));

        $sizes = wp_get_attachment_image_src($image->ID, $size);

        if ($size !== false) {
            $height = isset($sizes[1]) ? $sizes[1] : 0;
            $obj->setHeight($height);

            $width = isset($sizes[2]) ? $sizes[2] : 0;
            $obj->setWidth($width);
        }

        return $obj;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function toArray(): array
    {
        $headers = [
            '@context' => 'https://schema.org',
            '@type' => self::SCHEMA_TYPE
        ];

        return array_merge($headers, parent::toArray());
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return parent::parameters()->get('name', '');
    }

    /**
     * Set the value of name
     *
     * @return self
     */
    public function setName($name)
    {
        if (empty($name)) {
            parent::parameters()->remove('name');
        } else {
            parent::parameters()->set('name', (string) $name);
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getHeight(): int
    {
        return parent::parameters()->get('height', 0);
    }


    /**
     * @param integer|null $height
     * @return self
     */
    public function setHeight($height): self
    {
        if (is_numeric($height) && (int) $height > 0) {
            parent::parameters()->set('height', (int) $height);
        } else {
            parent::parameters()->remove('height');
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getWidth(): int
    {
        return parent::parameters()->get('width', 0);
    }

    /**
     * @param integer|null $width
     * @return self
     */
    public function setWidth($width): self
    {
        if (is_numeric($width) && (int) $width > 0) {
            parent::parameters()->set('width', (int) $width);
        } else {
            parent::parameters()->remove('width');
        }

        return $this;
    }
}
