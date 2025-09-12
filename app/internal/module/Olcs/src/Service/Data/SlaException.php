<?php

declare(strict_types=1);

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Api\Entity\Pi\SlaException as SlaExceptionEntity;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * SLA Exception data service
 */
class SlaException extends AbstractListDataService
{
    protected static $sort = 'slaDescription';
    protected static $order = 'ASC';

    /**
     * Fetch list data
     *
     * @param array|null $context Context parameters
     * @return array
     * @throws DataServiceException
     */
    public function fetchListData($context = null): array
    {
        $data = $this->getData('slaExceptions');

        if (!empty($data)) {
            return $data;
        }

        $params = [
            'sort' => static::$sort,
            'order' => static::$order,
        ];

        $response = $this->handleQuery(
            TransferQry\Cases\Pi\SlaExceptionList::create($params)
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();
        $slaExceptions = $result['results'] ?? [];

        $this->setData('slaExceptions', $slaExceptions);

        return $slaExceptions;
    }

    /**
     * Format data for optgroups - groups by SLA Description
     *
     * @param SlaExceptionEntity[]|array $data Raw data
     * @return array<string, array{label: string, options: array<int, string>}>
     */
    public function formatDataForGroups(array $data): array
    {
        $optGroups = [];

        foreach ($data as $item) {
            // Handle both entity objects and arrays from query results
            if ($item instanceof SlaExceptionEntity) {
                $id = $item->getId();
                $slaDescription = $item->getSlaDescription();
                $exceptionDescription = $item->getSlaExceptionDescription();
            } else {
                $id = $item['id'];
                $slaDescription = $item['slaDescription'];
                $exceptionDescription = $item['slaExceptionDescription'];
            }

            if (!isset($optGroups[$slaDescription])) {
                $optGroups[$slaDescription] = [
                    'label' => $slaDescription,
                    'options' => []
                ];
            }

            $optGroups[$slaDescription]['options'][$id] = $exceptionDescription;
        }

        return $optGroups;
    }

    /**
     * Fetch list options with optgroups
     *
     * @param array|string|null $context Context
     * @param bool $useGroups Use groups (always true for this service)
     * @return array<string, array{label: string, options: array<int, string>}>
     */
    public function fetchListOptions($context = null, $useGroups = true): array
    {
        $data = $this->fetchListData($context);

        if (empty($data)) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        // Fallback to simple format if groups not wanted
        return $this->formatData($data);
    }

    /**
     * Format data for simple select (fallback)
     *
     * @param array $data Data
     * @return array<int, string>
     */
    public function formatData(array $data): array
    {
        $optionData = [];

        foreach ($data as $item) {
            if ($item instanceof SlaExceptionEntity) {
                $id = $item->getId();
                $label = $item->getSlaDescription() . ' - ' . $item->getSlaExceptionDescription();
            } else {
                $id = $item['id'];
                $label = $item['slaDescription'] . ' - ' . $item['slaExceptionDescription'];
            }

            $optionData[$id] = $label;
        }

        return $optionData;
    }
}
