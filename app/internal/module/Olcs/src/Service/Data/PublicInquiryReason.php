<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

class PublicInquiryReason extends AbstractData implements ListDataInterface
{
    protected $serviceName = 'Reason';

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the data
     * as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore this
     * flag if the data doesn't allow for option groups to be constructed.
     *
     * @param mixed $context
     * @param bool $useGroups
     * @return array|void
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $context['bundle'] = json_encode(['properties' => 'ALL']);
        $data = $this->fetchPublicInquiryReasonData($context);
        $ret = [];

        if (!is_array($data) || !isset($data['Results']) || !is_array($data['Results'])) {
            return [];
        }

        foreach ($data['Results'] as $datum) {
            $ret[$datum['id']] = $datum['sectionCode'];
        }

        return $ret;
    }

    public function fetchPublicInquiryReasonData($params)
    {
        if (is_null($this->getData('pir'))) {

            $data = $this->getRestClient()->get('', $params);
            $this->setData('pir', $data);
        }

        return $this->getData('pir');

    }
}