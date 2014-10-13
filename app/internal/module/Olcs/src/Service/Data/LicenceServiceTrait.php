<?php

/**
 * Licence Service Trait
 */
namespace Olcs\Service\Data;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Licence Service Trait
 */
trait LicenceServiceTrait
{
    /**
     * @var \Olcs\Service\Data\Licence
     */
    protected $licenceService;

    /**
     * @param \Olcs\Service\Data\Licence $licenceService
     * @return $this
     */
    public function setLicenceService($licenceService)
    {
        $this->licenceService = $licenceService;
        return $this;
    }

    /**
     * @return \Olcs\Service\Data\Licence
     */
    public function getLicenceService()
    {
        return $this->licenceService;
    }

    /**
     * Get Licence Ni/Goods/Psv information
     *
     * @return array
     */
    protected function getLicenceContext()
    {
        $licence = $this->getLicenceService()->fetchLicenceData();

        return [
            'isNi' => $licence['niFlag'],
            'goodsOrPsv' => $licence['goodsOrPsv']['id'],
            'trafficArea' => $licence['trafficArea']['id']
        ];
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = parent::createService($serviceLocator);

        $service->setLicenceService($serviceLocator->get('Olcs\Service\Data\Licence'));

        return $service;
    }
}
