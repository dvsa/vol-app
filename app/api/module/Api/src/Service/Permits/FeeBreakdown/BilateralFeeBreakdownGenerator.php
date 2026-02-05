<?php

namespace Dvsa\Olcs\Api\Service\Permits\FeeBreakdown;

use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;

class BilateralFeeBreakdownGenerator implements FeeBreakdownGeneratorInterface
{
    private const array STANDARD_OR_CABOTAGE_KEY_MAPPINGS = [
        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 'standard',
        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 'cabotage'
    ];

    private const array PERMIT_USAGE_KEY_MAPPINGS = [
        RefData::JOURNEY_SINGLE => 'single',
        RefData::JOURNEY_MULTIPLE => 'multiple'
    ];

    /**
     * Create service instance
     *
     *
     * @return BilateralFeeBreakdownGenerator
     */
    public function __construct(private readonly FeeTypeRepository $feeTypeRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(IrhpApplication $irhpApplication)
    {
        $irhpPermitApplications = $irhpApplication->getIrhpPermitApplications();
        $rows = [];

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $countryName = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getCountryDesc();

            $bilateralRequired = $irhpPermitApplication->getFilteredBilateralRequired();
            foreach ($bilateralRequired as $requestedType => $quantity) {
                $feePerPermit = $this->getFeePerPermit($irhpPermitApplication, $requestedType);
                $total = $feePerPermit * $quantity;

                $rows[] = [
                    'countryName' => $countryName,
                    'type' => $this->generateTranslationKey($irhpPermitApplication, $requestedType),
                    'quantity' => $quantity,
                    'total' => $total,
                ];
            }
        }

        // sort the rows by country name, but leave the order of rows within each country unchanged
        $distinctCountryNames = array_unique(array_column($rows, 'countryName'));
        sort($distinctCountryNames);

        $sortedRows = [];
        foreach ($distinctCountryNames as $countryName) {
            foreach ($rows as $row) {
                if ($row['countryName'] == $countryName) {
                    $sortedRows[] = $row;
                }
            }
        }

        return $sortedRows;
    }

    /**
     * Get the fee amount per permit
     *
     * @param string $requestedType
     * @return int
     */
    public function getFeePerPermit(IrhpPermitApplication $irhpPermitApplication, $requestedType)
    {
        $irhpPermitStock = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock();

        $feeTypes = [];
        $productReferences = $irhpPermitApplication->getBilateralFeeProductReferences(
            $irhpPermitStock,
            $requestedType
        );

        foreach ($productReferences as $productReference) {
            $feeTypes[] = $this->feeTypeRepo->getLatestByProductReference($productReference);
        }

        return $irhpPermitApplication->getBilateralFeePerPermit($feeTypes);
    }

    /**
     * Generate a translation key for use in the type column
     *
     * @param string $requestedType
     * @return string
     */
    private function generateTranslationKey(IrhpPermitApplication $irhpPermitApplication, $requestedType)
    {
        if ($requestedType == IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED) {
            return $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getPeriodNameKey();
        }

        $permitUsage = $irhpPermitApplication->getBilateralPermitUsageSelection();

        return sprintf(
            'permits.irhp.range.type.%s.%s',
            self::STANDARD_OR_CABOTAGE_KEY_MAPPINGS[$requestedType],
            self::PERMIT_USAGE_KEY_MAPPINGS[$permitUsage]
        );
    }
}
