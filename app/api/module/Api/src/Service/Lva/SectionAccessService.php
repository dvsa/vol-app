<?php

/**
 * Section Access Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Lva;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Psr\Container\ContainerInterface;

/**
 * Section Access Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SectionAccessService implements FactoryInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Cache the sections
     *
     * @var array
     */
    private $sections;

    /**
     * @var  \Dvsa\Olcs\Api\Service\Lva\SectionConfig
     */
    protected $sectionConfig;

    /**
     * @var \Dvsa\Olcs\Api\Service\Lva\RestrictionService
     */
    private $restrictionService;

    /**
     * Get sections from section config
     *
     * @return array
     */
    private function getSections()
    {
        if ($this->sections === null) {
            $this->sections = $this->sectionConfig->getAll();
        }

        return $this->sections;
    }

    /**
     * Get a list of accessible sections
     *
     * @param array $access
     * @return array
     */
    public function getAccessibleSections(Application $application)
    {
        $lva = $application->isVariation() ? 'variation' : 'application';

        return $this->getAccessibleSectionsForLva($lva, $application->getLicence(), $application);
    }

    public function getAccessibleSectionsForLicence(Licence $licence)
    {
        return $this->getAccessibleSectionsForLva('licence', $licence, $licence);
    }

    public function getAccessibleSectionsForLicenceContinuation(Licence $licence)
    {
        return $this->getAccessibleSectionsForLva('continuation', $licence, $licence);
    }

    protected function getAccessibleSectionsForLva($lva, Licence $licence, $entity)
    {
        $location = $this->isGranted(Permission::INTERNAL_USER) ? 'internal' : 'external';

        $hasConditions = $licence->hasApprovedUnfulfilledConditions();

        $goodsOrPsv = null;
        if ($entity->getGoodsOrPsv() !== null) {
            $goodsOrPsv = $entity->getGoodsOrPsv()->getId();
        }

        $licenceType = null;
        if ($entity->getLicenceType() !== null) {
            $licenceType = $entity->getLicenceType()->getId();
        }

        $vehicleType = null;
        if ($entity->getVehicleType() !== null) {
            $vehicleType = $entity->getVehicleType()->getId();
        }

        $access = [
            $location,
            $lva,
            $goodsOrPsv,
            $licenceType,
            $vehicleType,
            $hasConditions ? 'hasConditions' : 'noConditions'
        ];

        if ($lva === 'variation') {
            $this->sectionConfig->setVariationCompletion($entity->getApplicationCompletion());
        }

        $sections = $this->getSections();

        foreach (array_keys($sections) as $section) {
            if (!$this->doesHaveAccess($section, $access)) {
                unset($sections[$section]);
            }
        }

        return $sections;
    }

    /**
     * Check if the licence has access to the section
     *
     * @param string $section
     * @return boolean
     */
    public function doesHaveAccess($section, array $access = [])
    {
        $sections = $this->getSections();

        $sectionDetails = $sections[$section];

        // If the section has no restrictions just return
        if (!isset($sectionDetails['restricted']) || empty($sectionDetails['restricted'])) {
            return true;
        }

        $restrictions = $sectionDetails['restricted'];

        return $this->restrictionService->isRestrictionSatisfied($restrictions, $access, $section);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->restrictionService = $container->get('RestrictionService');
        $this->sectionConfig = $container->get('SectionConfig');
        $this->setAuthService($container->get(AuthorizationService::class));
        return $this;
    }
}
