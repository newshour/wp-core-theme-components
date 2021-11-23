<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use Timber\User;

class PersonSchema extends AbstractSchema
{
    public const SCHEMA_TYPE = 'Person';

    /**
     * @return array
     */
    public function toArray(): array
    {
        $headers = [
            '@context' => 'https://schema.org',
            '@type' => $this->isPartnerOrganization() ? 'Organization' : self::SCHEMA_TYPE
        ];

        return array_merge($headers, parent::toArray());
    }

    /**
     * @param User $user
     * @return PersonSchema
     */
    public static function createFromTimberUser(User $user): PersonSchema
    {
        $obj = new PersonSchema();
        $obj->setFirstName($user->first_name)
            ->setLastName($user->last_name);

        $displayName = empty($user->name) ? implode(' ', [$user->first_name, $user->last_name]) : $user->name;
        $obj->setDisplayName($displayName);

        $partnerOrganizations = apply_filters('core_theme_partner_organizations', []);

        if (!empty($displayName) && is_array($partnerOrganizations) && count($partnerOrganizations) > 0) {
            $regExOrgs = array_map(fn ($item) => preg_quote(trim($item)), $partnerOrganizations);
            $count = preg_match(
                '/\,\s+?(' . implode('|', $regExOrgs) . ')/i',
                $user->name,
                $matches
            );

            if (isset($matches[1])) {
                $orgSchema = new OrganizationSchema();
                $orgSchema->setName($matches[1]);
                $obj->setDisplayName(str_ireplace($matches[0], '', $user->name))
                    ->attachSchema('affiliation', $orgSchema);
            } elseif (in_array($user->name, $partnerOrganizations)) {
                $orgSchema = new OrganizationSchema();
                $orgSchema->setName($user->name);
                $obj->attachSchema('affiliation', $orgSchema);
            }
        }

        return $obj;
    }

    /**
     * @return boolean
     */
    public function isPartnerOrganization(): bool
    {
        $partnerOrganizations = apply_filters('core_theme_partner_organizations', []);

        if (!is_array($partnerOrganizations) || count($partnerOrganizations) < 1) {
            return false;
        }

        return in_array($this->getDisplayName(), $partnerOrganizations);
    }

    /**
     * Get the value of firstName
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return parent::parameters()->get('firstName', '');
    }

    /**
     * @param string $firstName
     * @return self
     */
    public function setFirstName($firstName): self
    {
        if (empty($firstName)) {
            parent::parameters()->remove('firstName');
        } else {
            parent::parameters()->set('firstName', (string) $firstName);
        }

        return $this;
    }

    /**
     * Get the value of lastName
     *
     * @return string
     */
    public function getLastName(): string
    {
        return parent::parameters()->get('lastName', '');
    }

    /**
     * @param string $lastName
     * @return self
     */
    public function setLastName($lastName): self
    {
        if (empty($lastName)) {
            parent::parameters()->remove('lastName');
        } else {
            parent::parameters()->set('lastName', (string) $lastName);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return parent::parameters()->get('displayName', '');
    }

    /**
     * @param string $displayName
     * @return self
     */
    public function setDisplayName($displayName): self
    {
        if (empty($displayName)) {
            parent::parameters()->remove('displayName');
        } else {
            parent::parameters()->set('displayName', (string) $displayName);
        }

        return $this;
    }
}
