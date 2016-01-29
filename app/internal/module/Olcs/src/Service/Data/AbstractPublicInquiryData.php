<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Common\Service\Data\LicenceServiceTrait;
use Common\Service\Data\ApplicationServiceTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PublicInquiryReason
 * @package Olcs\Service\Data
 */
abstract class AbstractPublicInquiryData extends AbstractData implements ListDataInterface
{
    use LicenceServiceTrait;
    use ApplicationServiceTrait;

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

        $licenceId = $this->getLicenceService()->getId();

        if ($licenceId !== null) {
            if (empty($context['goodsOrPsv'])) {
                if (empty($this->getApplicationService()->getId())) {
                    // find an application linked to the licence
                    $applicationsForLicence = $this->getServiceLocator()
                        ->get('Entity\Application')
                        ->getApplicationsForLicence($licenceId);

                    if (!empty($applicationsForLicence['Results'])) {
                        $app = array_pop($applicationsForLicence['Results']);
                        $this->getApplicationService()->setId($app['id']);

                        $appContext = $this->getApplicationContext();

                        // use application's goodsOrPsv instead
                        $context['goodsOrPsv'] = $appContext['goodsOrPsv'];
                        $context['isNi'] = $appContext['isNi'] === 'Y';
                    }
                }
            } else {
                // @NOTE: we need booleans to filter properly...
                $context['isNi'] = $context['isNi'] === 'Y';
            }
        }

        $context['bundle'] = json_encode([]);
        $context['limit'] = 1000;

        /**
         * @todo [OLCS-5306] check this, it appears to be an invalid order by
        $context['order'] = 'sectionCode';
         */
        $context['sort'] = 'sectionCode';

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

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->setLicenceService($serviceLocator->get('\Common\Service\Data\Licence'));
        $this->setApplicationService($serviceLocator->get('\Common\Service\Data\Application'));

        return $this;
    }
}
