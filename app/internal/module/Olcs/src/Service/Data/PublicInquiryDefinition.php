<?php

namespace Olcs\Service\Data;

/**
 * Class PublicInquiryDefinition
 * @package Olcs\Service\Data
 */
class PublicInquiryDefinition extends AbstractPublicInquiryData
{
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
