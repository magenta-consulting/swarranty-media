<?php

namespace Magenta\Bundle\SWarrantyModelBundle\Entity\System;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="system__system")
 */
class System
{
    const NOTIFICATION_WARRANTY_NEW_REGISTRATION = 'WARRANTY_NEW_REGISTRATION';
    const NOTIFICATION_TECHNICIAN_NEW_ASSIGNMENT = 'TECHNICIAN_NEW_ASSIGNMENT';

    public $notificationTypes = [];

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=180)
     */
    protected $id = 'magenta.swarranty';

    public function __construct()
    {
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $domain = 'magentapulse.com';

    /**
     * @var string
     * @ORM\Column(type="string", length=255, options={"default":"DEFAULT_ADMIN_TOKEN"})
     */
    protected $adminToken = 'DEFAULT_ADMIN_TOKEN';

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $sslEnabled = false;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Magenta\Bundle\SWarrantyModelBundle\Entity\System\SystemModule", mappedBy="system", cascade={"persist","merge"}, orphanRemoval=true)
     */
    protected $modules;

    /**
     * @param $code
     *
     * @return SystemModule|null
     */
    public function getModuleByCode($code)
    {
        $code = strtoupper($code);
        /** @var SystemModule $module */
        foreach ($this->modules as $module) {
            if ($module->getModuleCode() === $code) {
                return $module;
            }
        }

        return null;
    }

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Magenta\Bundle\SWarrantyModelBundle\Entity\Organisation\Organisation", mappedBy="system", cascade={"persist","merge"}, orphanRemoval=true)
     */
    protected $organisations;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $lastNotifiedAt;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Collection
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function setModules(Collection $modules): void
    {
        $this->modules = $modules;
    }

    /**
     * @return Collection
     */
    public function getOrganisations(): Collection
    {
        return $this->organisations;
    }

    public function setOrganisations(Collection $organisations): void
    {
        $this->organisations = $organisations;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return bool
     */
    public function isSslEnabled(): bool
    {
        return $this->sslEnabled;
    }

    public function setSslEnabled(bool $sslEnabled): void
    {
        $this->sslEnabled = $sslEnabled;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastNotifiedAt(): ?\DateTime
    {
        return $this->lastNotifiedAt;
    }

    public function setLastNotifiedAt(?\DateTime $lastNotifiedAt): void
    {
        $this->lastNotifiedAt = $lastNotifiedAt;
    }

    /**
     * @return string
     */
    public function getAdminToken(): string
    {
        return $this->adminToken;
    }

    public function setAdminToken(string $adminToken): void
    {
        $this->adminToken = $adminToken;
    }
}
