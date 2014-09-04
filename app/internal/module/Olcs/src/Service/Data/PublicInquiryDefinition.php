<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

class PublicInquiryDefinition extends AbstractData implements ListDataInterface
{
    protected $serviceName = 'PublicInquiryDefinition';

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
        $bundle = [
            'properties' => 'ALL',
            'conditions' => []
        ];

        //fetch + format data
    }

    public function fetchPublicInquiryDefinitionData($bundle = null)
    {
        if (is_null($this->getData('pid'))) {

            $bundle = is_null($bundle)? $this->getBundle() : $bundle;
            $data = $this->getRestClient()->get('/', ['bundle' => json_encode($bundle)]);
            $this->setData('pid', $data);
        }

        return $this->getData('pid');

    }

    public function getBundle()
    {

    }
}