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
 * @package Olcs\Service
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
     * @param $category
     * @param bool $useGroups
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
     * Filters out all options but those allowable / implemented
     *
     * @param null $context
     * @param bool $useGroups
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
     * Format data!
     *
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
     * Format for groups
     *
     * @param array $data
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
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setRefDataService($serviceLocator->get('\Common\Service\Data\RefData'));

        return $this;
    }

    /**
     * @param string $refDataService
     */
    public function setRefDataService($refDataService)
    {
        $this->refDataService = $refDataService;
    }

    /**
     * @return string
     */
    public function getRefDataService()
    {
        return $this->refDataService;
    }
}
