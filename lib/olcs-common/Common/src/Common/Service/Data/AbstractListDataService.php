<?php

namespace Common\Service\Data;

use Common\Service\Data\Interfaces\ListData;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
abstract class AbstractListDataService extends AbstractDataService implements ListData
{
    /**
     * Create service instance
     *
     *
     * @return AbstractDataService
     */
    public function __construct(AbstractListDataServiceServices $abstractListDataServiceServices)
    {
        parent::__construct($abstractListDataServiceServices->getAbstractDataServiceServices());
    }

    /**
     * Format data for groups
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatDataForGroups(array $data)
    {
        $groups = [];
        $optionData = [];

        foreach ($data as $datum) {
            //false if null or not in array
            if (isset($datum['parent']['id'])) {
                $groups[$datum['parent']['id']][] = $datum;
            } else {
                $optionData[$datum['id']] = ['label' => $datum['description'], 'options' => []];
            }
        }

        foreach ($groups as $parent => $groupData) {
            $optionData[$parent]['options'] = $this->formatData($groupData);
        }

        return $optionData;
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
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    #[\Override]
    public function fetchListOptions($context = null, $useGroups = false)
    {
        $data = $this->fetchListData($context);

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
     * @param array $context Context
     *
     * @return array
     */
    abstract public function fetchListData($context = null);
}
