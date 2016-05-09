<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\FactoryInterface;
use Common\Service\Data\LicenceServiceTrait;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\PiDefinitionList as PiDefinitionListDto;

/**
 * Class PublicInquiryDefinition
 * @package Olcs\Service\Data
 */
class PublicInquiryDefinition extends AbstractPublicInquiryData implements ListDataInterface, FactoryInterface
{
    protected $listDto = PiDefinitionListDto::class;
    protected $sort = 'sectionCode';
    protected $order = 'ASC';

    /**
     * @var string
     */
    protected $serviceName = 'PiDefinition';

    /**
     * @param $data
     * @return array
     */
    public function formatDataForGroups($data)
    {
        $groups = [];
        $optionData = [];

        foreach ($data as $datum) {
            if (isset($datum['piDefinitionCategory'])) {
                $groups[$datum['piDefinitionCategory']][] = $datum;
            }
        }

        foreach ($groups as $parent => $groupData) {
            $optionData[$parent]['options'] = $this->formatData($groupData);
            $optionData[$parent]['label'] = $parent;
        }
        return $optionData;
    }
}
