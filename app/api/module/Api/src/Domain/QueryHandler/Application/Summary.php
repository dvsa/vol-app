<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Utils\Helper\ValueHelper;

class Summary extends AbstractQueryHandler
{
    public const ACTION_PRINT_SIGN_RETURN = 'PRINT_SIGN_RETURN';
    public const ACTION_SUPPLY_SUPPORTING_EVIDENCE = 'SUPPLY_SUPPORTING_EVIDENCE';
    public const ACTION_APPROVE_TM = 'APPROVE_TM';

    public const MISSING_EVIDENCE_OC = 'MISSING_EVIDENCE_OC';
    public const MISSING_EVIDENCE_PSV_SMALL = 'MISSING_EVIDENCE_PSV_SMALL';
    public const MISSING_EVIDENCE_PSV_MAIN_OCCUPATION = 'markup-main-occupation-evidence-proof';
    public const MISSING_EVIDENCE_FINANCIAL = 'markup-financial-standing-proof';

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Fee', 'Cases'];

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Application\Summary $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Application\Application $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $actions = $this->determineActions($application);

        $bundle = [
            'licence',
            'status'
        ];

        if (array_key_exists(self::ACTION_APPROVE_TM, $actions)) {
            $bundle['transportManagers'] = [
                'tmApplicationStatus',
                'transportManager' => [
                    'homeCd' => [
                        'person'
                    ]
                ]
            ];

            if ($application->isVariation()) {
                $criteria = Criteria::create();
                $criteria->where(
                    $criteria->expr()->in(
                        'action',
                        [
                            Entity\Application\ApplicationOperatingCentre::ACTION_ADD,
                            Entity\Application\ApplicationOperatingCentre::ACTION_UPDATE
                        ]
                    )
                );

                $bundle['transportManagers']['criteria'] = $criteria;
            }
        }

        // Check if auto-granted and return early with special response
        if ($application->getWasAutoGranted()) {
            return $this->result(
                $application,
                $bundle,
                [
                    'wasAutoGranted' => true,
                    'autoGrantChanges' => $application->getAutoGrantChangeSummary(),
                    'actions' => [],
                    'reference' => $this->getLatestPaymentReference($application->getId()),
                    'outstandingFee' => false,
                    'canWithdraw' => false
                ]
            );
        }

        return $this->result(
            $application,
            $bundle,
            [
                'actions' => $actions,
                'reference' => $this->getLatestPaymentReference($application->getId()),
                'outstandingFee' => $application->getLatestOutstandingApplicationFee() !== null,
                'canWithdraw' => $this->canWithdraw($application)
            ]
        );
    }

    /**
     * Determine Actions
     *
     * @param Entity\Application\Application $application Application object
     *
     * @return array
     */
    protected function determineActions(Entity\Application\Application $application)
    {
        $actions = [];

        if ($this->needsToSign($application)) {
            $actions[self::ACTION_PRINT_SIGN_RETURN] = self::ACTION_PRINT_SIGN_RETURN;
        }

        $missingEvidence = $this->determineMissingEvidence($application);
        if (!empty($missingEvidence)) {
            $actions[self::ACTION_SUPPLY_SUPPORTING_EVIDENCE] = $missingEvidence;
        }

        if ($this->needsToApproveTms($application)) {
            $actions[self::ACTION_APPROVE_TM] = self::ACTION_APPROVE_TM;
        }

        return $actions;
    }

    /**
     * Define is application need to be signed
     *
     * @param Entity\Application\Application $application Application object
     *
     * @return bool
     */
    protected function needsToSign(Entity\Application\Application $application)
    {
        if ($application->isVariation()) {
            return false;
        }

        if (ValueHelper::isOn($application->getAuthSignature())) {
            return false;
        }

        if ($application->isDigitallySigned()) {
            return false;
        }

        return true;
    }

    /**
     * Determine missing Evidence
     *
     * @param Entity\Application\Application $application Application object
     *
     * @return array
     */
    protected function determineMissingEvidence(Entity\Application\Application $application)
    {
        if ($application->getLicenceType()->getId() === Entity\Licence\Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return [];
        }
        $evidence = [];

        if ($application->canAddOperatingCentresEvidence()) {
            $evidence[] = self::MISSING_EVIDENCE_OC;
        }

        if ($application->canAddFinancialEvidence()) {
            $evidence[] = self::MISSING_EVIDENCE_FINANCIAL;
        }

        if ($application->canAddPsvSmallEvidence()) {
            $evidence[] = self::MISSING_EVIDENCE_PSV_SMALL;
        }

        if ($application->canAddPsvLargeEvidence()) {
            $evidence[] = self::MISSING_EVIDENCE_PSV_MAIN_OCCUPATION;
        }

        return $evidence;
    }

    /**
     * Define is Needs To Approve Tms
     *
     * @param Entity\Application\Application $application Application object
     *
     * @return bool
     */
    private function needsToApproveTms(Entity\Application\Application $application)
    {
        if ($application->getLicenceType()->getId() === Entity\Licence\Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return false;
        }

        $tms = $application->getTransportManagers()->filter(
            function ($element) use ($application) {
                $result = !in_array(
                    $element->getTmApplicationStatus(),
                    [
                        Entity\Tm\TransportManagerApplication::STATUS_OPERATOR_SIGNED,
                        Entity\Tm\TransportManagerApplication::STATUS_RECEIVED
                    ]
                );

                if ($result && $application->isVariation()) {
                    $result = in_array($element->getAction(), ['A', 'U']);
                }

                return $result;
            }
        );

        return $tms->isEmpty() === false;
    }

    /**
     * Return reference number of latest payment
     *
     * @param int $appId Application Id
     *
     * @return null|string
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getLatestPaymentReference($appId)
    {
        /** @var Repository\Fee $repo */
        $repo = $this->getRepo('Fee');

        /** @var Entity\Fee\Fee $latestFee */
        $latestFee = $repo->fetchLatestPaidFeeByApplicationId($appId);
        if ($latestFee) {
            return $latestFee->getLatestPaymentRef();
        }

        return '';
    }

    private function canWithdraw(Entity\Application\Application $application)
    {

        $status = $application->getStatus()->getId();
        $underConsideration = $this->getRepo()->getRefdataReference($application::APPLICATION_STATUS_UNDER_CONSIDERATION)->getId();
        $isUnderConsideration = ($status === $underConsideration);

        /**
         * @var Repository\Cases $caseRepository
         */
        $caseRepository = $this->getRepo('Cases');

        $openCases = $caseRepository->fetchOpenCasesForApplication($application->getId());

        if (count($openCases) > 0) {
            return false;
        }
        return $isUnderConsideration;
    }
}
