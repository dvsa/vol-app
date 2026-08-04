<?php

namespace Common\Service\Data;

/**
 * Licence Service Trait
 */
trait LicenceServiceTrait
{
    /**
     * @var \Common\Service\Data\Licence
     */
    protected $licenceService;

    /**
     * Set licence service
     *
     * @param \Common\Service\Data\Licence $licenceService Licence service
     *
     * @return $this
     */
    public function setLicenceService($licenceService)
    {
        $this->licenceService = $licenceService;

        return $this;
    }

    /**
     * Get licence service
     *
     * @return \Common\Service\Data\Licence
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

        //not ideal, but this is used by many data services, so this was the way requiring far fewer fixes elsewhere
        if (!isset($licence['id'])) {
            return [];
        }

        return [
            'isNi' => $licence['niFlag'],
            'goodsOrPsv' => $licence['goodsOrPsv']['id'],
            'trafficArea' => $licence['trafficArea']['id']
        ];
    }
}
