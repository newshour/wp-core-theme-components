<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

class OrganizationSchema extends AbstractSchema {

    const SCHEMA_TYPE = 'Organization';

    private bool $isPublisher = false;
    private bool $isNewsMediaOrg = false;

    /**
     * @param boolean $isPublisher Optional
     */
    public function __construct($isPublisher = false) {

        $this->isPublisher = (bool) $isPublisher;

    }

    /**
     * Creates OrganizationSchema obj from default Wordpress values found in the General
     * settings page.
     *
     * @see https://wordpress.org/support/article/settings-general-screen/
     * @return OrganizationSchema
     */
    public static function createFromBlogInfo(): OrganizationSchema {

        $obj = new OrganizationSchema(true);
        $obj->setName(get_bloginfo('name'));
        $obj->setUrl(get_bloginfo('url'));

        $image = new ImageSchema();
        $image->setUrl(get_option('core_theme_org_logo_url', ''));
        $obj->setLogo($image);

        return $obj;

    }

    /**
     * @return array
     */
    public function toArray(): array {

        $headers = [
            '@context' => 'https://schema.org',
            '@type' => $this->getIsNewsMediaOrg() ? 'NewsMediaOrganization' : self::SCHEMA_TYPE,
        ];

        if (!empty($url = $this->getUrl())) {
            $headers['@id'] = $this->isPublisher() ? $url . '#publisher' : $url . '#organization';
        }

        return array_merge($headers, parent::toArray());

    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string {

        return parent::parameters()->get('name', '');

    }

    /**
     * Set the value of name
     *
     * @param string $name
     * @return self
     */
    public function setName($name) {

        if (empty($name)) {

            parent::parameters()->remove('name');

        } else {

            parent::parameters()->set('name', (string) $name);

        }

        return $this;
    }

    /**
     * Get the value of logo
     *
     * @return ImageSchema
     */
    public function getLogo(): ImageSchema {

        return parent::parameters()->get('logo', new ImageSchema());

    }

    /**
     * @param ImageSchema|null $logo
     * @return self
     */
    public function setLogo(ImageSchema $logo = null): self {

        if ($logo instanceof ImageSchema && !$logo->isEmpty()) {

            parent::parameters()->set('logo', $logo);

        } else {

            parent::parameters()->remove('logo');

        }

        return $this;

    }

    /**
     * @return boolean
     */
    public function isPublisher(): bool {

        return $this->isPublisher;

    }

    /**
     * @param boolean $isPublisher
     * @return self
     */
    public function setIsPublisher($isPublisher = true): self {

        $this->isPublisher = $isPublisher;

        return $this;

    }

    /**
     * @return boolean
     */
    public function getIsNewsMediaOrg(): bool {

        return $this->isNewsMediaOrg;

    }

    /**
     * @param boolean $isNewsMediaOrg
     * @return self
     */
    public function isNewsMediaOrg($isNewsMediaOrg = true): self {

        $this->isNewsMediaOrg = $isNewsMediaOrg;

        return $this;

    }

}
