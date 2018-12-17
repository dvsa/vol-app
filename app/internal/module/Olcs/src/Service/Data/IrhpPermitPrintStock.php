<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock;

/**
 * Class IrhpPermitPrintStock
 *
 * @package Olcs\Service\Data
 */
class IrhpPermitPrintStock extends AbstractDataService implements ListDataInterface
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
            $optionData[$datum['id']] = sprintf(
                '%s (%s to %s)',
                $datum['irhpPermitType']['name']['description'],
                $datum['validFrom'],
                $datum['validTo']
            );
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
        if (is_null($this->getData('IrhpPermitPrintStock'))) {
            $dtoData = ReadyToPrintStock::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('IrhpPermitPrintStock', $response->getResult()['results']);
        }

        return $this->getData('IrhpPermitPrintStock');
    }
}
