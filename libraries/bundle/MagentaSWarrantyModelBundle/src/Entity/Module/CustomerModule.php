<?php

namespace Magenta\Bundle\SWarrantyModelBundle\Entity\Module;


use Doctrine\ORM\Mapping as ORM;
use Magenta\Bundle\SWarrantyModelBundle\Entity\AccessControl\ACEntry;
use Magenta\Bundle\SWarrantyModelBundle\Entity\AccessControl\ACModuleInterface;
use Magenta\Bundle\SWarrantyModelBundle\Entity\Customer\Customer;
use Magenta\Bundle\SWarrantyModelBundle\Entity\Organisation\OrganisationMember;
use Magenta\Bundle\SWarrantyModelBundle\Entity\System\SystemModule;
use Magenta\Bundle\SWarrantyModelBundle\Entity\User\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="module__customer")
 */
class CustomerModule extends SystemModule implements ACModuleInterface {
	public function isUserGranted(OrganisationMember $member= null, $permission, $object, $class): ?bool {
		if( ! $this->isClassSupported($class)) {
			return null;
		}
		
		return parent::isUserGranted($member, $permission, $object, $class);
		
	}
	
	public function isClassSupported(string $class): bool {
		return $class === Customer::class;
	}
	
	public function getSupportedModuleActions(): array {
		return ACEntry::getSupportedActions();
	}
	
	public function getModuleName(): string {
		return 'Customer Management';
	}
	
	public function getModuleCode(): string {
		return 'CUSTOMER';
	}
}