<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\ListDataInterface;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintRangeType;

/**
 * Class IrhpPermitPrintRangeType
 *
 * @package Olcs\Service\Data
 */
class IrhpPermitPrintRangeType extends AbstractDataService implements ListDataInterface
{
    /**
     * @var TranslationHelperService
     */
    private $translator;

    /**
     * @var int
     */
    private $irhpPermitStock;

    /**
     * Create service instance
     *
     * @param AbstractDataServiceServices $abstractDataServiceServices
     * @param TranslationHelperService $translationHelperService
     *
     * @return IrhpPermitPrintRangeType
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        TranslationHelperService $translationHelperService
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->translator = $translationHelperService;
    }

    /**
     * Set Irhp Permit Stock
     *
     * @param int $irhpPermitStock Irhp Permit Stock
     *
     * @return $this
     */
    public function setIrhpPermitStock($irhpPermitStock)
    {
        $this->irhpPermitStock = $irhpPermitStock;
        return $this;
    }

    /**
     * Get Irhp Permit Stock
     *
     * @return int
     */
    public function getIrhpPermitStock()
    {
        return $this->irhpPermitStock;
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
            $optionData[$datum] = $this->translator->translate('permits.irhp.range.type.' . $datum);
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
     * @throw DataServiceException
     */
    public function fetchListData()
    {
        if (is_null($this->getData('IrhpPermitPrintRangeType'))) {
            $dtoData = ReadyToPrintRangeType::create(
                [
                    'irhpPermitStock' => $this->irhpPermitStock,
                ]
            );
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('IrhpPermitPrintRangeType', $response->getResult()['results'] ?? null);
        }

        return $this->getData('IrhpPermitPrintRangeType');
    }
}
