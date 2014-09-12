<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PublicInquiryReason
 * @package Olcs\Service\Data
 */
abstract class AbstractPublicInquiryData extends AbstractData implements ListDataInterface
{
    /**
     * @var \Olcs\Service\Data\Licence
     */
    protected $licenceService;

    /**
     * @param \Olcs\Service\Data\Licence $licenceService
     * @return $this
     */
    public function setLicenceService($licenceService)
    {
        $this->licenceService = $licenceService;
        return $this;
    }

    /**
     * @return \Olcs\Service\Data\Licence
     */
    public function getLicenceService()
    {
        return $this->licenceService;
    }

    /**
     * Still might not be the best place for this, however its the only place it's currently used
     *
     * @return array
     */
    protected function getLicenceContext()
    {
        $licence = $this->getLicenceService()->fetchLicenceData();

        return [
            'isNi' => $licence['niFlag'],
            'goodsOrPsv' => $licence['goodsOrPsv']['id']
        ];
    }

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
        $context = empty($context)? $this->getLicenceContext() : $context;
        $context['bundle'] = json_encode(['properties' => 'ALL']);

        $data = $this->fetchPublicInquiryData($context);
        $ret = [];

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
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = parent::createService($serviceLocator);

        $service->setLicenceService($serviceLocator->get('Olcs\Service\Data\Licence'));

        return $service;
    }

    /**
     * @param array $data
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['sectionCode'];
        }

        return $optionData;
    }

    public function formatDataForGroups($data)
    {
        return $this->formatData($data);
    }
}
