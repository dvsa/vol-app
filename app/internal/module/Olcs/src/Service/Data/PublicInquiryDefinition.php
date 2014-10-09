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

    /**
     * @param $context
     * @param bool $useGroups
     * @return array
     */
    /*public function fetchListOptions($context, $useGroups = false)
    {
        $context = empty($context) ?
            $this->getLicenceContext() : array_merge($context, $this->getLicenceContext());
        $context['bundle'] = json_encode(['properties' => 'ALL']);
        $context['order'] = 'sectionCode';

        $data = $this->fetchPublicInquiryData($context);

        if (!$data) {
            return [];
        }

        return $this->formatData($data);
    }*/

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @internal param $category
     * @return array
     */
    /*public function fetchListData($params)
    {
        if (is_null($this->getData('PiDefinition'))) {

            $data = $this->getRestClient()->get('', $params);

            $this->setData('PiDefinition', false);

            if (isset($data['Results'])) {
                $this->setData('PiDefinition', $data['Results']);
            }
        }

        return $this->getData('PiDefinition');
    }*/
}
