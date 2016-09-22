<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionActionTypes
 * Provides list options of submission action types
 *
 * @package Olcs\Service\Data
 */
class SubmissionActionTypes extends AbstractDataService implements FactoryInterface, ListDataInterface
{
    /**
     * Ref data category ID for submission action types
     */
    const EBSR_REF_DATA_CATEGORY_ID = 'sub_st_rec';

    /**
     * RefData Service
     * @var string
     */
    protected $refDataService;

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Fetch list data
     *
     * @return array
     */
    public function fetchListData()
    {
        $allOptions =  $this->getRefDataService()->fetchListData(self::EBSR_REF_DATA_CATEGORY_ID);

        $this->setData('SubmissionActionTypes', false);

        if (isset($allOptions)) {
            $this->setData('SubmissionActionTypes', $allOptions);
        }

        return $this->getData('SubmissionActionTypes');
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
     * Format for groups
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatDataForGroups(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $parentId = $datum['parent']['id'];

            if (!isset($optionData[$parentId])) {
                $optionData[$parentId] = [
                    'label' => $datum['parent']['description'],
                    'options' => []
                ];
            }

            $optionData[$parentId]['options'][$datum['id']] = $datum['description'];
        }

        return $optionData;
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
        $this->setRefDataService($serviceLocator->get('\Common\Service\Data\RefData'));

        return $this;
    }

    /**
     * Set ref data service
     *
     * @param string $refDataService Ref data service
     *
     * @return $this
     */
    public function setRefDataService($refDataService)
    {
        $this->refDataService = $refDataService;

        return $this;
    }

    /**
     * Get ref data service
     *
     * @return string
     */
    public function getRefDataService()
    {
        return $this->refDataService;
    }
}
