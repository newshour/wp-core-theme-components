<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use SplObjectStorage;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Timber\Image;
use Timber\TextHelper;
use NewsHour\WPCoreThemeComponents\Utilities;
use NewsHour\WPCoreThemeComponents\Components\Component;

/**
 * Abstract schema class for schema.org objects. Note that only a small sub-set of schema.org objects
 * are provided with this library.
 *
 * @see https://schema.org
 */
abstract class AbstractSchema implements Schema, Component
{
    private array $images = [];
    private array $sameAs = [];
    private bool $asHtml = false;
    private ParameterBag $parameterBag;

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->asHtml) {
            return Utilities::createLdJsonTag($this->toArray());
        }

        return $this->render();
    }

    /**
     * Serializes to JSON.
     *
     * @return string
     */
    public function render(): string
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        return $serializer->serialize($this->toArray(), 'json');
    }

    /**
     * Returns the object's data into key value pairs based on the underlying
     * schema.org entity structure.
     *
     * @return array
     */
    public function toArray(): array
    {
        $final = [];

        if ($this->isEmpty() || !isset($this->parameterBag)) {
            return $final;
        }

        foreach ($this->parameterBag as $k => $v) {
            if ($v instanceof Schema) {
                $final[$k] = $v->toArray();
            } elseif ($v instanceof SchemaCollection && $v->count() > 0) {
                $final[$k] = $v->toArray();
            } elseif ($v instanceof Carbon) {
                $final[$k] = $v->format('c');
            } else {
                $final[$k] = $v;
            }
        }

        return $final;
    }

    /**
     * Checks if the object is considered "empty". Objects are considered "empty" when they have no set
     * schema.org properties set, excluding identifiers (e.g. keys beginnging with '@'). Objects with
     * only identifiers set will be considered "empty".
     *
     * @see https://schema.org/identifier
     * @return boolean
     */
    public function isEmpty(): bool
    {
        if ($this->parameters()->count() < 1) {
            return true;
        }

        $generator = fn ($array) => (yield from $array);

        foreach ($generator($this->parameters()->keys()) as $key) {
            if (!TextHelper::starts_with($key, '@')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Attach a child schema.
     *
     * @param string $key
     * @param Schema|null $schema
     * @return self
     */
    public function attachSchema(string $key, ?Schema $schema = null): self
    {
        if ($schema->isEmpty()) {
            $this->parameters()->remove($key);
        } else {
            $this->parameters()->set($key, $schema);
        }

        return $this;
    }

    /**
     * Add an identifier.
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function addIdentifier($key, $value): self
    {
        $_key = TextHelper::starts_with('@', $key) ? $key : '@' . trim($key);
        $this->parameters()->set($_key, trim((string) $value));
        return $this;
    }

    /**
     * Get the "published on" datetime.
     *
     * @return Carbon|null
     */
    public function getDatePublished(): ?Carbon
    {
        return $this->parameters()->get('datePublished', null);
    }

    /**
     * Set the "published on" datetime.
     *
     * @param Carbon|string|null $datePublished
     * @param string $timezone Optional
     * @return self
     */
    public function setDatePublished($datePublished, $timezone = '')
    {
        if (empty($datePublished)) {
            $this->parameters()->remove('datePublished');
        } else {
            $this->parameters()->set(
                'datePublished',
                Utilities::toCarbonObj($datePublished, $timezone)
            );
        }

        return $this;
    }

    /**
     * Get the "modified on" datetime.
     *
     * @return Carbon|null
     */
    public function getDateModified(): ?Carbon
    {
        return $this->parameters()->get('dateModified', null);
    }

    /**
     * Set the "published on" datetime.
     *
     * @param Carbon|string $dateModified
     * @param string $timezone Optional
     * @return self
     */
    public function setDateModified($dateModified, $timezone = ''): self
    {
        if (empty($dateModified)) {
            $this->parameters()->remove('dateModified');
        } else {
            $this->parameters()->set(
                'dateModified',
                Utilities::toCarbonObj($dateModified, $timezone)
            );
        }

        return $this;
    }

    /**
     * Get the value of inLanguage (e.g. en_US, etc).
     *
     * @return string
     */
    public function getInLanguage(): string
    {
        return $this->parameters()->get('inLanguage', '');
    }

    /**
     * @param string|null $inLanguage
     * @return self
     */
    public function setInLanguage($inLanguage): self
    {
        if (empty($inLanguage)) {
            $this->parameters()->remove('inLanguage');
        } else {
            $this->parameters()->set('inLanguage', (string) $inLanguage);
        }

        return $this;
    }

    /**
     * Returns a set of "sameAs" values.
     *
     * @see https://schema.org/sameAs
     * @return array
     */
    public function getSameAs(): array
    {
        return $this->parameters()->get('sameAs', []);
    }

    /**
     * Add a "sameAs" URL string or array of URL strings.
     *
     * @see https://schema.org/sameAs
     * @param array|string $url
     * @return self
     */
    public function addSameAs($url): self
    {
        if (empty($url)) {
            return $this;
        }

        if (is_array($url)) {
            $this->parameters()->set('sameAs', Utilities::stringArrayUnique($url, true));
            return $this;
        }

        $cleaned = trim($url);
        $current = $this->parameters()->get('sameAs', []);

        if (!in_array($cleaned, $current)) {
            $current[] = $cleaned;
            $this->parameters()->set('sameAs', $current);
        }

        return $this;
    }

    /**
     * Set the value of publisher.
     *
     * @param OrganizationSchema|string|null $publisher
     * @return self
     */
    public function setPublisher($publisher = null): self
    {
        if (is_string($publisher)) {
            $organization = new OrganizationSchema();
            $organization->setName($publisher);
            $publisher = $organization;
        }

        $this->attachSchema('publisher', $publisher);

        return $this;
    }

    /**
     * Get the value of description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->parameters()->get('description', '');
    }

    /**
     * Set the value of description.
     *
     * @param string|null $description
     * @return self
     */
    public function setDescription($description): self
    {
        if (empty($description)) {
            $this->parameters()->remove('description');
        } else {
            $this->parameters()->set('description', (string) $description);
        }

        return $this;
    }

    /**
     * Returns a collection of PersonSchema objects (authors).
     *
     * @return SchemaCollection
     */
    public function getAuthors(): SplObjectStorage
    {
        return $this->parameters()->get('author', new SchemaCollection());
    }

    /**
     * Add a PersonSchema object (author).
     *
     * @param PersonSchema $person
     * @return self
     */
    public function addAuthor(PersonSchema $person): self
    {
        if (!$this->getAuthors()->contains($person)) {
            $this->getAuthors()->attach($person);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->parameters()->get('url', '');
    }

    /**
     * @param string|null $url
     * @return self
     */
    public function setUrl($url): self
    {
        if (empty($url)) {
            $this->parameters()->remove('url');
        } else {
            $this->parameters()->set('url', (string) $url);
        }

        return $this;
    }

    /**
     * Returns a set of image URLs.
     *
     * @return array
     */
    public function getImages(): array
    {
        return $this->parameters()->get('image', []);
    }

    /**
     * Add an image URL.
     *
     * @param ImageSchema|Image|string $image
     * @return self
     */
    public function addImage($image): self
    {
        $url = '';

        if (is_array($image)) {
            $this->parameters()->set('image', Utilities::stringArrayUnique($image, true));
            return $this;
        } elseif (is_string($image)) {
            $url = $image;
        } elseif ($image instanceof ImageSchema) {
            $url = $image->getUrl();
        }

        if (empty($url)) {
            return $this;
        }

        $images = $this->parameters()->get('image', []);

        if (!in_array($url, $images)) {
            $images[] = $url;
            $this->parameters()->set('image', $images);
        }

        return $this;
    }

    /**
     * Returns the thumbnail URL.
     *
     * @return string
     */
    public function getThumbnail(): string
    {
        return $$this->parameters()->get('thumbnailUrl', '');
    }

    /**
     * Set the thumbnail URL.
     *
     * @param mixed ImageSchema|Image|string $thumbnail
     * @return self
     */
    public function setThumbnail($thumbnail = null): self
    {
        $schema = $this->imageToSchema($thumbnail);

        if (empty($schema) || $schema->isEmpty()) {
            $this->parameters()->remove('thumbnailUrl');
        } else {
            $this->parameters()->set('thumbnailUrl', $schema->getUrl());
        }

        return $this;
    }

    /**
     * Set a "potentialAction" schema.
     *
     * @see https://schema.org/potentialAction
     * @param Schema|null $potentialAction
     * @return self
     */
    public function setPotentialAction(?Schema $potentialAction = null): self
    {
        $this->attachSchema('potentialAction', $potentialAction);

        return $this;
    }

    /**
     * @return OrganizationSchema
     */
    public function getOrganization(): OrganizationSchema
    {
        return $this->parameters()->get('organization', new OrganizationSchema());
    }

    /**
     * @param OrganizationSchema|null $organization
     * @return self
     */
    public function setOrganization(?OrganizationSchema $organization = null): self
    {
        $this->attachSchema('organization', $organization);

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAsHtml(): bool
    {
        return $this->asHtml;
    }

    /**
     * Set true to return output as HTML.
     *
     * @param bool $asHtml Optional, default is true.
     * @return self
     */
    public function asHtml($asHtml = true): self
    {
        $this->asHtml = (bool) $asHtml;

        return $this;
    }

    /**
     * @return ParameterBag
     */
    protected function parameters(): ParameterBag
    {
        if (!isset($this->parameterBag)) {
            $this->parameterBag = new ParameterBag();
        }

        return $this->parameterBag;
    }

    /**
     * Converts a Timber/Image object or string into an ImageSchema object.
     *
     * @param Image|string $image
     * @return ImageSchema|null
     */
    private function imageToSchema($image): ?ImageSchema
    {
        if (!empty($image)) {
            if ($image instanceof ImageSchema) {
                return $image;
            }

            if ($image instanceof Image) {
                return ImageSchema::createFromImageObj($image);
            }

            if (is_string($image)) {
                $schema = new ImageSchema();
                $schema->setUrl($image);
                return $schema;
            }
        }

        return null;
    }
}
