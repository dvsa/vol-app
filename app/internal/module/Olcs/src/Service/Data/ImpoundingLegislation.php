<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\RefData;
use Common\Service\Data\LicenceServiceTrait;

/**
 * Class ImpoundingLegislation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ImpoundingLegislation extends RefData implements ListDataInterface
{
    use LicenceServiceTrait;

    /**
    * @param mixed $context
    * @param bool $useGroups
    * @return array|void
    */
    public function fetchListOptions($context, $useGroups = false)
    {
        $context = empty($context)? $this->getLicenceContext() : $context;

        //decide which ref data category we need
        if (empty($context)) {
            $data = $this->fetchListData('impound_legislation_goods_gb');
        } elseif ($context['goodsOrPsv'] == 'lcat_psv') {
            $data = $this->fetchListData('impound_legislation_psv_gb');
        } elseif ($context['isNi'] == 'Y') {
            $data = $this->fetchListData('impound_legislation_goods_ni');
        } else {
            $data = $this->fetchListData('impound_legislation_goods_gb');
        }

        if (!is_array($data)) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $category
     * @return array
     */
    public function fetchListData($category = null)
    {
        if (is_null($this->getData($category))) {
            $data = $this->getRestClient()->get(sprintf('category/%s', $category));
            $this->setData($category, $data);
        }

        return $this->getData($category);
    }
}
