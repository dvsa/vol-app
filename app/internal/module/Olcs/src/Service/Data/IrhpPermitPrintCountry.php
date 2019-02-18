<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintCountry;

/**
 * Class IrhpPermitPrintCountry
 *
 * @package Olcs\Service\Data
 */
class IrhpPermitPrintCountry extends AbstractDataService implements ListDataInterface
{
    /**
     * @var int
     */
    private $irhpPermitType;

    /**
     * Set Irhp Permit Type
     *
     * @param int $irhpPermitType Irhp Permit Type
     *
     * @return $this
     */
    public function setIrhpPermitType($irhpPermitType)
    {
        $this->irhpPermitType = $irhpPermitType;
        return $this;
    }

    /**
     * Get Irhp Permit Type
     *
     * @return int
     */
    public function getIrhpPermitType()
    {
        return $this->irhpPermitType;
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
            $optionData[$datum['id']] = $datum['countryDesc'];
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
     * @throw UnexpectedResponseException
     */
    public function fetchListData()
    {
        if (is_null($this->getData('IrhpPermitPrintCountry'))) {
            $dtoData = ReadyToPrintCountry::create(['irhpPermitType' => $this->getIrhpPermitType()]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('IrhpPermitPrintCountry', $response->getResult()['results']);
        }

        return $this->getData('IrhpPermitPrintCountry');
    }
}
