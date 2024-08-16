<?php

namespace Dvsa\Olcs\Api\Service\Permits\FeeBreakdown;

use DateTime;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;

class MultilateralFeeBreakdownGenerator implements FeeBreakdownGeneratorInterface
{
    /** @var IrhpApplication */
    private $irhpApplication;

    /** @var array */
    private $issueFeeMappings = [];

    /** @var DateTime */
    private $issueFeeInvoicedDate;

    /** @var FeeType */
    private $applicationFeeType;

    /**
     * {@inheritdoc}
     */
    public function generate(IrhpApplication $irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        $this->applicationFeeType = $this->irhpApplication->getLatestOutstandingApplicationFee()->getFeeType();

        $issueFees = $this->irhpApplication->getOutstandingIssueFees();
        $this->populateIssueFeeMappings($issueFees);
        $this->issueFeeInvoicedDate = $issueFees[0]->getInvoicedDate(true);

        $rows = [];
        foreach ($this->irhpApplication->getIrhpPermitApplications() as $irhpPermitApplication) {
            if ($irhpPermitApplication->countPermitsRequired() > 0) {
                $rows[] = $this->generateRow($irhpPermitApplication);
            }
        }

        return $rows;
    }

    /**
     * Populates a key/value lookup array of product references to fee types
     */
    private function populateIssueFeeMappings(array $issueFees)
    {
        foreach ($issueFees as $issueFee) {
            $issueFeeType = $issueFee->getFeeType();
            $this->issueFeeMappings[$issueFeeType->getProductReference()] = $issueFeeType;
        }
    }

    /**
     * Returns an array representing a table row within the fee breakdown
     *
     *
     * @return array
     */
    private function generateRow(IrhpPermitApplication $irhpPermitApplication)
    {
        $issueFeeProductReference = $irhpPermitApplication->getIssueFeeProductReference($this->issueFeeInvoicedDate);

        $feePerPermit = $this->irhpApplication->getFeePerPermit(
            $this->applicationFeeType,
            $this->issueFeeMappings[$issueFeeProductReference]
        );

        $numberOfPermits = $irhpPermitApplication->countPermitsRequired();
        $irhpPermitStock = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock();
        $irhpPermitStockValidFrom = $irhpPermitStock->getValidFrom(true);
        $irhpPermitStockValidTo = $irhpPermitStock->getValidTo(true);

        $validFrom = $irhpPermitStockValidFrom;
        if ($this->issueFeeInvoicedDate > $irhpPermitStockValidFrom) {
            $validFrom = $this->issueFeeInvoicedDate;
        }

        return [
            'year' => $irhpPermitStockValidTo->format('Y'),
            'validFromTimestamp' => $validFrom->getTimestamp(),
            'validToTimestamp' => $irhpPermitStockValidTo->getTimestamp(),
            'feePerPermit' => $feePerPermit,
            'numberOfPermits' => $numberOfPermits,
            'totalFee' => $numberOfPermits * $feePerPermit,
        ];
    }
}
