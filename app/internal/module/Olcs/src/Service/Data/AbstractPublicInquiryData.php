<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Common\Service\Data\LicenceServiceTrait;

/**
 * Class PublicInquiryReason
 * @package Olcs\Service\Data
 */
abstract class AbstractPublicInquiryData extends AbstractData implements ListDataInterface
{
    use LicenceServiceTrait;

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the
     * data as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore
     * this flag if the data doesn't allow for option groups to be constructed.
     *
     * @param mixed $context
     * @param bool $useGroups
     * @return array|void
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $context = empty($context) ?
            $this->getLicenceContext() : array_merge($context, $this->getLicenceContext());
        $context['bundle'] = json_encode(['properties' => 'ALL']);
        $context['limit'] = 1000;
        $context['order'] = 'sectionCode';

        $data = $this->fetchPublicInquiryData($context);

        if (!is_array($data)) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * @param $params
     * @return array
     */
    public function fetchPublicInquiryData($params)
    {
        if (is_null($this->getData('pid'))) {

            $data = $this->getRestClient()->get('', $params);
            $this->setData('pid', false);
            if (isset($data['Results'])) {
                $this->setData('pid', $data['Results']);
            }
        }

        return $this->getData('pid');
    }

    /**
     * @param array $data
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['description'];
        }

        return $optionData;
    }

    /**
     * @param $data
     * @return array
     */
    public function formatDataForGroups($data)
    {
        $groups = [];
        $optionData = [];

        foreach ($data as $datum) {
            if (isset($datum['sectionCode'])) {
                $groups[$datum['sectionCode']][] = $datum;
            }
        }

        foreach ($groups as $parent => $groupData) {
            $optionData[$parent]['options'] = $this->formatData($groupData);
            $optionData[$parent]['label'] = $parent;
        }
        return $optionData;
    }
}
