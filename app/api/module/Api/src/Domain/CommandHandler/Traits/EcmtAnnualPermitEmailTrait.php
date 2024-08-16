<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Fees\DaysToPayIssueFeeProvider;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * ECMT Annual Permit email trait
 */
trait EcmtAnnualPermitEmailTrait
{
    /** @var DaysToPayIssueFeeProvider */
    private $daysToPayIssueFeeProvider;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->daysToPayIssueFeeProvider = $container->get('PermitsFeesDaysToPayIssueFeeProvider');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }

    /**
     * Get template variables
     *
     * @param IrhpApplication $recordObject
     *
     * @return array
     */
    protected function getTemplateVariables($recordObject): array
    {
        $daysToPayIssueFee = $this->daysToPayIssueFeeProvider->getDays();

        $vars = [
            // http://selfserve is replaced based on the environment
            'appUrl' => 'http://selfserve/',
            'permitsUrl' => 'http://selfserve/permits',
            'guidanceUrl' => 'https://www.gov.uk/guidance/international-authorisations-and-permits-for-road-haulage',
            'ecmtGuidanceUrl' => 'https://www.gov.uk/guidance/ecmt-international-road-haulage-permits',
            'applicationRef' => $recordObject->getApplicationRef(),
            'applicationFee' => $this->formatCurrency(
                $this->getRepo('FeeType')->getLatestByProductReference(FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF)->getAmount()
            ),
            'paymentDeadlineNumDays' => $daysToPayIssueFee,
        ];

        if ($recordObject->isAwaitingFee()) {
            /** @var IrhpPermitApplication $irhpPermitApplication */
            $irhpPermitApplication = $recordObject->getFirstIrhpPermitApplication();

            $vars['awaitingFeeUrl'] = sprintf(
                'http://selfserve/permits/application/%s/awaiting-fee',
                (int)$recordObject->getId()
            );
            $vars['euro5PermitsRequired'] = $irhpPermitApplication->getRequiredEuro5();
            $vars['euro6PermitsRequired'] = $irhpPermitApplication->getRequiredEuro6();
            $vars['euro5PermitsGranted'] = $irhpPermitApplication->countPermitsAwarded(
                RefData::EMISSIONS_CATEGORY_EURO5_REF
            );
            $vars['euro6PermitsGranted'] = $irhpPermitApplication->countPermitsAwarded(
                RefData::EMISSIONS_CATEGORY_EURO6_REF
            );
            $vars['permitsRequired'] = $recordObject->calculateTotalPermitsRequired();
            $vars['permitsGranted'] = $irhpPermitApplication->countPermitsAwarded();
            $vars['validityYear'] = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getValidityYear();

            $criteria = Criteria::create();
            $criteria->where(
                $criteria->expr()->eq(
                    'feeStatus',
                    $this->getRepo()->getRefdataReference(FeeEntity::STATUS_OUTSTANDING)
                )
            );

            $fees = $recordObject->getFees()->matching($criteria);
            $feeTypesAmounts = [];

            /** @var FeeEntity $fee */
            foreach ($fees as $fee) {
                if ($fee->isEcmtIssuingFee()) {
                    $feeTypesAmounts[] = [
                        'issueFeeAmount' => $fee->getFeeTypeAmount(),
                        'issueFeeTotal' => $fee->getOutstandingAmount(),
                        'invoicedDate' => $fee->getInvoicedDateTime()
                    ];
                }
            }

            if (count($feeTypesAmounts) !== 1) {
                throw new \Exception('There should be exactly one issuing fee.');
            }

            $vars['issueFeeDeadlineDate'] = $this->calculateDueDate(
                $feeTypesAmounts[0]['invoicedDate'],
                $daysToPayIssueFee
            );

            $vars['issueFeeAmount'] = $this->formatCurrency($feeTypesAmounts[0]['issueFeeAmount']);
            $vars['issueFeeTotal'] = $this->formatCurrency($feeTypesAmounts[0]['issueFeeTotal']);
        }

        return $vars;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTranslateToWelsh($recordObject)
    {
        return $recordObject->getLicence()->getTranslateToWelsh();
    }

    /**
     * Format a fee as currency
     *
     * param float $amount
     *
     * @return array
     */
    private function formatCurrency($amount)
    {
         return str_replace('.00', '', $amount);
    }
}
