<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationUndertakingsReviewService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Get Continuation Detail
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'ContinuationDetail';
    protected $extraRepos = ['SystemParameter', 'Fee', 'Document'];

    /**
     * @var FinancialStandingHelperService
     */
    private $financialStandingHelper;

    /**
     * @var ApplicationUndertakingsReviewService
     */
    private $reviewService;

    /**
     * Handle query
     *
     * @param QueryInterface $query DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var ContinuationDetailEntity $continuationDetail */
        $continuationDetail = $this->getRepo()->fetchUsingId($query);
        $licence = $continuationDetail->getLicence();
        $documents = $this->getRepo('Document')
            ->fetchListForContinuationDetail($continuationDetail->getId(), Query::HYDRATE_ARRAY);
        $continuationFees = $this->getRepo('Fee')->fetchOutstandingContinuationFeesByLicenceId($licence->getId());
        $signatureDetails = [];
        if ($continuationDetail->getDigitalSignature()) {
            $signatureDetails = [
                'name' => $continuationDetail->getDigitalSignature()->getSignatureName(),
                'date' => $continuationDetail->getDigitalSignature()->getCreatedOn(),
                'dob' => $continuationDetail->getDigitalSignature()->getDateOfBirth(),
            ];
        }

        $financeRequired = $this->financialStandingHelper->getFinanceCalculationForOrganisation(
            $licence->getOrganisation()->getId()
        );

        return $this->result(
            $continuationDetail,
            [
                'licence' => [
                    'organisation',
                    'trafficArea',
                    'licenceType',
                    'goodsOrPsv',
                ]
            ],
            [
                'financeRequired' => $financeRequired,
                'disableCardPayments' => $this->getRepo('SystemParameter')->getDisableSelfServeCardPayments(),
                'fees' => $this->resultList(
                    $continuationFees,
                    [
                        'feeType' => [
                            'feeType'
                        ],
                        'licence'
                    ]
                ),
                'documents' => $documents,
                'organisationTypeId' => $licence->getOrganisation()->getType()->getId(),
                'declarations' => $this->reviewService->getDeclarationMarkup($continuationDetail),
                'disableSignatures' => $this->getRepo('SystemParameter')->getDisableGdsVerifySignatures(),
                'hasOutstandingContinuationFee' => count($continuationFees) > 0,
                'signature' => $signatureDetails,
                'reference' => $this->getPaymentReference($licence->getId()),
                'isFinancialEvidenceRequired' =>
                    $financeRequired > (
                        $continuationDetail->getAverageBalanceAmount()
                        + $continuationDetail->getFactoringAmount()
                        + $continuationDetail->getOverdraftAmount()
                        + $continuationDetail->getOtherFinancesAmount()
                    ),
                'isPhysicalSignature' =>
                    $continuationDetail->getSignatureType() !== null
                    && $continuationDetail->getSignatureType()->getId() === RefData::SIG_PHYSICAL_SIGNATURE,
                'conditionsUndertakings' => $licence->getGroupedConditionsUndertakings(),
            ]
        );
    }

    /**
     * Return reference number of latest payment
     *
     * @param int $licenceId Licence id
     *
     * @return null|string
     */
    private function getPaymentReference($licenceId)
    {
        /** @var FeeRepo $repo */
        $repo = $this->getRepo('Fee');

        /** @var FeeEntity $latestFee */
        $latestFee = $repo->fetchLatestPaidContinuationFee($licenceId);
        if ($latestFee) {
            $today = (new DateTime('now'))->setTime(0, 0, 0);
            $latestTransaction = $latestFee->getLatestTransaction();
            if (!$latestTransaction) {
                return null;
            }
            $feeDate = $latestTransaction->getCompletedDate(true)->setTime(0, 0, 0);
            $plus30Days = $feeDate->add(new \DateInterval('P30D'));
            if ($plus30Days < $today) {
                return null;
            }
            return $latestFee->getLatestPaymentRef();
        }

        return null;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Get
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->financialStandingHelper = $container->get('FinancialStandingHelperService');
        $this->reviewService = $container->get('ContinuationReview\Declaration');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
