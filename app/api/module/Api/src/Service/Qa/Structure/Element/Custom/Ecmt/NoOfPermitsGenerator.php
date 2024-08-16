<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityCounter;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockLicenceMaxPermittedCounter;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class NoOfPermitsGenerator implements ElementGeneratorInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsGenerator
     */
    public function __construct(private FeeTypeRepository $feeTypeRepo, private NoOfPermitsFactory $noOfPermitsFactory, private EmissionsCategoryConditionalAdder $emissionsCategoryConditionalAdder, private StockAvailabilityCounter $stockAvailabilityCounter, private StockLicenceMaxPermittedCounter $stockLicenceMaxPermittedCounter)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $irhpApplication = $context->getQaEntity();

        $irhpPermitApplication = $irhpApplication->getFirstIrhpPermitApplication();
        $irhpPermitStock = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock();
        $irhpPermitStockId = $irhpPermitStock->getId();
        $licence = $irhpApplication->getLicence();

        $applicationFeeType = $this->feeTypeRepo->getLatestByProductReference(
            $irhpApplication->getApplicationFeeProductReference()
        );

        $issueFee = 'N/A';
        if ($irhpApplication->isOngoing()) {
            $issueFeeType = $this->feeTypeRepo->getLatestByProductReference(
                $irhpApplication->getIssueFeeProductReference()
            );

            $issueFee = $issueFeeType->getFixedValue();
        }

        // validation against the remaining stock should be skipped
        // when the application is APSG and under consideration
        $skipAvailabilityValidation = $irhpApplication->isApsg() && $irhpApplication->isUnderConsideration();

        $maxPermitted = $this->stockLicenceMaxPermittedCounter->getCount($irhpPermitStock, $licence);

        $maxCanApplyFor = $maxPermitted;
        $stockAvailability = $this->stockAvailabilityCounter->getCount($irhpPermitStockId);
        if ($stockAvailability < $maxCanApplyFor) {
            $maxCanApplyFor = $stockAvailability;
        }

        $noOfPermits = $this->noOfPermitsFactory->create(
            $maxCanApplyFor,
            $maxPermitted,
            $applicationFeeType->getFixedValue(),
            $issueFee,
            $skipAvailabilityValidation
        );

        $this->emissionsCategoryConditionalAdder->addIfRequired(
            $noOfPermits,
            'euro5',
            $irhpPermitApplication->getRequiredEuro5(),
            RefData::EMISSIONS_CATEGORY_EURO5_REF,
            $irhpPermitStockId
        );

        $this->emissionsCategoryConditionalAdder->addIfRequired(
            $noOfPermits,
            'euro6',
            $irhpPermitApplication->getRequiredEuro6(),
            RefData::EMISSIONS_CATEGORY_EURO6_REF,
            $irhpPermitStockId
        );

        return $noOfPermits;
    }
}
