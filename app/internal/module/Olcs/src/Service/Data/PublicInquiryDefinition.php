<?php

namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Cases\Pi\PiDefinitionList as PiDefinitionListDto;

/**
 * Class Public Inquiry Definition
 *
 * @package Olcs\Service\Data
 */
class PublicInquiryDefinition extends AbstractPublicInquiryData
{
    /**
     * @var string
     */
    protected $listDto = PiDefinitionListDto::class;

    /**
     * @var string
     */
    protected $sort = 'sectionCode';

    /**
     * @var string
     */
    protected $order = 'ASC';

    /**
     * Format data for groups
     *
     * @param array $data Data
     *
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
