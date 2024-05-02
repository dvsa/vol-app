<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\ListDataInterface;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock;

/**
 * Class IrhpPermitPrintStock
 *
 * @package Olcs\Service\Data
 */
class IrhpPermitPrintStock extends AbstractDataService implements ListDataInterface
{
    public const COUNTRY_ID_MOROCCO = 'MA';

    /**
     * @var int
     */
    private $irhpPermitType;

    /**
     * @var string
     */
    private $country;

    /**
     * Create service instance
     *
     * @param AbstractDataServiceServices $abstractDataServiceServices
     * @param TranslationHelperService $translator
     *
     * @return IrhpPermitPrintStock
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        private TranslationHelperService $translator
    ) {
        parent::__construct($abstractDataServiceServices);
    }

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
            $optionData[$datum['id']] = $this->generateLabel($datum);
        }

        return $optionData;
    }

    /**
     * Generate a label for an item in the returned data
     *
     *
     * @return string
     */
    private function generateLabel(array $datum)
    {
        if ($this->getCountry() == self::COUNTRY_ID_MOROCCO) {
            return $this->translator->translate($datum['periodNameKey']);
        }

        return (!empty($datum['validFrom']) && !empty($datum['validTo']))
            ? sprintf('%s to %s', $datum['validFrom'], $datum['validTo'])
            : sprintf('Stock %s', $datum['id']);
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
     * @throw DataServiceException
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
                throw new DataServiceException('unknown-error');
            }

            $this->setData('IrhpPermitPrintStock', $response->getResult()['results'] ?? null);
        }

        return $this->getData('IrhpPermitPrintStock');
    }
}
