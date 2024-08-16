<?php

namespace Olcs\Service\Data;

use Common\Service\Data\Licence;
use Common\Service\Data\LicenceServiceTrait;
use Common\Service\Data\RefData;
use Common\Service\Data\RefDataServices;

/**
 * Class ImpoundingLegislation
 *
 * @package Olcs\Service\Data
 */
class ImpoundingLegislation extends RefData
{
    use LicenceServiceTrait;

    /**
     * Create service instance
     *
     * @param RefDataServices $refDataServices
     * @param Licence $licenceDataService
     *
     * @return ImpoundingLegislation
     */
    public function __construct(
        RefDataServices $refDataServices,
        Licence $licenceDataService
    ) {
        parent::__construct($refDataServices);
        $this->setLicenceService($licenceDataService);
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchListOptions($context = null, $useGroups = false)
    {
        $params = empty($context) ? $this->getLicenceContext() : $context;

        //decide which ref data category we need
        if (empty($params)) {
            $data = $this->fetchListData('impound_legislation_goods_gb');
        } elseif ($params['goodsOrPsv'] == 'lcat_psv') {
            $data = $this->fetchListData('impound_legislation_psv_gb');
        } elseif ($params['isNi'] == 'Y') {
            $data = $this->fetchListData('impound_legislation_goods_ni');
        } else {
            $data = $this->fetchListData('impound_legislation_goods_gb');
        }

        if (!is_array($data)) {
            return [];
        }

        return $this->formatData($data);
    }
}
