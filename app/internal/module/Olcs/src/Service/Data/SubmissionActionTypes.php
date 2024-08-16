<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\ListDataInterface;
use Common\Service\Data\RefData;

/**
 * Class SubmissionActionTypes
 * Provides list options of submission action types
 *
 * @package Olcs\Service\Data
 */
class SubmissionActionTypes extends AbstractDataService implements ListDataInterface
{
    /**
     * Ref data category ID for submission action types
     */
    public const EBSR_REF_DATA_CATEGORY_ID = 'sub_st_rec';

    /** @var RefData */
    protected $refDataService;

    /**
     * Create service instance
     *
     * @param AbstractDataServiceServices $abstractDataServiceServices
     * @param RefData $refDataService
     *
     * @return SubmissionActionTypes
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        RefData $refDataService
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->refDataService = $refDataService;
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        $allOptions =  $this->refDataService->fetchListData(self::EBSR_REF_DATA_CATEGORY_ID);

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
}
