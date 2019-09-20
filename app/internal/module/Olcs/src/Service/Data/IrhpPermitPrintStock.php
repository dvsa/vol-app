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
     * @var int
     */
    private $irhpPermitType;

    /**
     * @var string
     */
    private $country;

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
     * Set country
     *
     * @param string $country Country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
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
            $label = (!empty($datum['validFrom']) && !empty($datum['validTo']))
                ? sprintf('%s to %s', $datum['validFrom'], $datum['validTo'])
                : sprintf('Stock %s', $datum['id']);

            $optionData[$datum['id']] = $label;
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
        if (is_null($this->getData('IrhpPermitPrintStock'))) {
            $dtoData = ReadyToPrintStock::create(
                [
                    'irhpPermitType' => $this->irhpPermitType,
                    'country' => $this->country,
                ]
            );
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('IrhpPermitPrintStock', $response->getResult()['results']);
        }

        return $this->getData('IrhpPermitPrintStock');
    }
}
