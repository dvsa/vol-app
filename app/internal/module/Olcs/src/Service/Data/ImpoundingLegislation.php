<?php

namespace Olcs\Service\Data;

use Common\Service\Data\LicenceServiceTrait;
use Common\Service\Data\RefData;
use Zend\ServiceManager\FactoryInterface;

/**
 * Class ImpoundingLegislation
 *
 * @package Olcs\Service\Data
 */
class ImpoundingLegislation extends RefData implements FactoryInterface
{
    use LicenceServiceTrait;

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $params = empty($context)? $this->getLicenceContext() : $context;

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
