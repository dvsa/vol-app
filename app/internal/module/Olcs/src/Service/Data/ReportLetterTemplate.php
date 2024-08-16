<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\DocTemplate\GetList;

/**
 * Class ReportLetterTemplate
 *
 * @package Olcs\Service\Data
 */
class ReportLetterTemplate extends AbstractDataService implements ListData
{
    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    private function formatData(array $data)
    {
        $optionData = [];
        foreach ($data as $datum) {
            $optionData[$datum['templateSlug']] = $datum['templateSlug'];
        }
        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param array|string|null $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchListOptions($context = null, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Fetch list data
     *
     * @return array
     * @throws DataServiceException
     */
    private function fetchListData()
    {
        if (is_null($this->getData('ReportLetterTemplate'))) {
            $query = GetList::create(['category' => CategoryDataService::CATEGORY_REPORT, 'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_LETTER]);
            $response = $this->handleQuery($query);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('ReportLetterTemplate', $response->getResult()['results']);
        }

        return $this->getData('ReportLetterTemplate');
    }
}
