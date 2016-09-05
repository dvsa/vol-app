<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ApplicationServiceTrait;
use Common\Service\Data\LicenceServiceTrait;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Abstract Public Inquiry Data
 *
 * @package Olcs\Service\Data
 */
abstract class AbstractPublicInquiryData extends AbstractDataService implements ListDataInterface, FactoryInterface
{
    use LicenceServiceTrait;
    use ApplicationServiceTrait;

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the
     * data as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore
     * this flag if the data doesn't allow for option groups to be constructed.
     *
     * @param array $context   Context
     * @param bool  $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $params = empty($context) ? $this->getLicenceContext() : array_merge($context, $this->getLicenceContext());

        $licenceId = $this->getLicenceService()->getId();

        if ($licenceId !== null) {
            if (empty($params['goodsOrPsv'])) {
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
                        $params['goodsOrPsv'] = $appContext['goodsOrPsv'];
                    }
                }
            }
        } else {
            $params['goodsOrPsv'] = 'NULL';
        }

        $data = $this->fetchPublicInquiryData($params);

        if (!is_array($data)) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Fetch public inquiry data
     *
     * @param array $params Params
     *
     * @return array
     */
    public function fetchPublicInquiryData($params)
    {
        if (is_null($this->getData('pid'))) {

            $result = $this->fetchListData($params);

            if (isset($result['results'])) {
                $this->setData('pid', $result['results']);
            }
        }

        return $this->getData('pid');
    }

    /**
     * Method to make the backend call to retrieve the PI data based on listDto set in the child class
     *
     * @param array $params Params
     *
     * @return array
     * @throws UnexpectedResponseException
     */
    public function fetchListData(array $params)
    {
        $listDto = $this->listDto;

        $dtoData = $listDto::create(
            array_merge(
                $params,
                [
                    'sort' => $this->sort,
                    'order' => $this->order
                ]
            )
        );

        $response = $this->handleQuery($dtoData);

        if (!$response->isOk()) {
            throw new UnexpectedResponseException('unknown-error');
        }

        return $response->getResult();
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
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
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return void
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
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->setLicenceService($serviceLocator->get('\Common\Service\Data\Licence'));
        $this->setApplicationService($serviceLocator->get('\Common\Service\Data\Application'));

        return $this;
    }
}
