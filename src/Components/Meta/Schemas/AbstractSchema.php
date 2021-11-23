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
     * @return string
     */
    public function render(): string
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        return $serializer->serialize($this->toArray(), 'json');
    }

    /**
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
     * Get the value of datePublished
     *
     * @return Carbon|null
     */
    public function getDatePublished(): ?Carbon
    {
        return $this->parameters()->get('datePublished', null);
    }

    /**
     * Set the value of datePublished
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
     * Get the value of dateModified
     *
     * @return Carbon|null
     */
    public function getDateModified(): ?Carbon
    {
        return $this->parameters()->get('dateModified', null);
    }

    /**
     * Set the value of dateModified
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
     * Get the value of inLanguage
     *
     * @return string
     */
    public function getInLanguage(): string
    {
        return $this->parameters()->get('inLanguage', '');
    }

    /**
     * Set the value of inLanguage
     *
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
     * Get the value of sameAs
     *
     * @return array
     */
    public function getSameAs(): array
    {
        return $this->sameAs;
    }

    /**
     * @param array|string $url
     * @return self
     */
    public function addSameAs($url): self
    {
        if (empty($url)) {
            return $this;
        }

        if (is_array($url)) {
            $this->parameters()->set('sameAs', array_map('trim', $url));
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
     * Set the value of publisher
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
      * Get the value of description
      *
      * @return string
      */
    public function getDescription(): string
    {
        return $this->parameters()->get('description', '');
    }

    /**
     * Set the value of description
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
     * Get the value of url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->parameters()->get('url', '');
    }

    /**
     * Set the value of url
     *
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
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param ImageSchema|Image|string $image
     * @return self
     */
    public function addImage($image): self
    {
        $url = '';

        if (is_string($image)) {
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
     * Get the value of thumbnail
     *
     * @return string
     */
    public function getThumbnail(): string
    {
        return $$this->parameters()->get('thumbnailUrl', '');
    }

    /**
     * Set the value of thumbnail
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

    protected function parameters(): ParameterBag
    {
        if (!isset($this->parameterBag)) {
            $this->parameterBag = new ParameterBag();
        }

        return $this->parameterBag;
    }

    /**
     * @param array $dict
     * @return array
     */
    protected function cleanSchemaDict(array $dict): array
    {
        $cleaned = array_filter($dict);
        array_walk($cleaned, fn (&$v) => is_string($v) ? trim($v) : $v);

        return $cleaned;
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
