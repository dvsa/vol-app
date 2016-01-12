<?php

namespace Olcs\Service\Data;

use Common\Service\Data\LicenceServiceTrait;
use Dvsa\Olcs\Transfer\Query\RefData\RefDataList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Zend\ServiceManager\FactoryInterface;
use Common\Service\Data\RefData;

/**
 * Class ImpoundingLegislation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ImpoundingLegislation extends RefData implements FactoryInterface
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
}
