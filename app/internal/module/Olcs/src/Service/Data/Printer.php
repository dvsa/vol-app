<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\Printer\PrinterList;

/**
 * Printer data service
 *
 * @package Olcs\Service\Data
 */
class Printer extends AbstractDataService implements ListDataInterface
{
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
            $optionData[$datum['id']] = $datum['printerName'];
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
    public function fetchListOptions($context, $useGroups = false)
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
     * @throw UnexpectedResponseException
     */
    public function fetchListData()
    {
        if (is_null($this->getData('Printer'))) {
            $dtoData = PrinterList::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('Printer', false);
            $result = $response->getResult();

            if (isset($result['results'])) {
                $this->setData('Printer', $result['results']);
            }
        }

        return $this->getData('Printer');
    }
}
