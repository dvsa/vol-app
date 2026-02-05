<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Psr\Container\ContainerInterface;

/**
 * @see \Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence\ContinueLicenceTest
 */
final class ContinueLicence extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['ContinuationDetail', 'GoodsDisc'];

    /**
     * @var FinancialStandingHelperService
     */
    private $financialStandingHelper;

    /**
     * Handle command
     *
     * @param CommandInterface $command DTO
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        assert($command instanceof \Dvsa\Olcs\Transfer\Command\Licence\ContinueLicence);

        /* @var $licence Licence */
        // check Licence ID and version
        $licence = $this->getRepo()->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());

        $continuationDetails = $this->getRepo('ContinuationDetail')->fetchForLicence($command->getId());
        if (count($continuationDetails) === 0) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\RuntimeException('No ContinuationDetail found');
        }
        $continuationDetail = $continuationDetails[0];

        //Add 5 years to the continuation and review dates
        $licence->setExpiryDate((new DateTime($licence->getExpiryDate()))->modify('+5 years'));
        $licence->setReviewDate((new DateTime($licence->getReviewDate()))->modify('+5 years'));

        $result = new Result();
        if ($licence->isGoods()) {
            $this->processGoods($licence, $continuationDetail, $result);
        } else {
            $this->processPsv($licence, $continuationDetail, $result);
        }
        $this->getRepo()->save($licence);

        // Print licence
        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Transfer\Command\Licence\PrintLicence::create(['id' => $licence->getId()])
            )
        );

        $continuationDetail->setStatus(
            $this->getRepo()->getRefdataReference(ContinuationDetail::STATUS_COMPLETE)
        );
        $this->getRepo('ContinuationDetail')->save($continuationDetail);

        /** @var ContinuationDetail $continuationDetail */
        if ($continuationDetail->getIsDigital()) {
            $createQueueCmd = CreateQueueCmd::create(
                [
                    'entityId' => $continuationDetail->getId(),
                    'type' => QueueEntity::TYPE_CREATE_CONTINUATION_SNAPSHOT,
                    'status' => QueueEntity::STATUS_QUEUED
                ]
            );
            $result->merge($this->handleSideEffect($createQueueCmd));
            $result->merge($this->createTaskForSignature($continuationDetail));
            $this->createTaskForInsufficientFinances($continuationDetail, $result);
            $this->createTaskForOtherFinances($continuationDetail, $result);
        }

        $this->clearLicenceCaches($licence);
        $result->addMessage('Licence ' . $licence->getId() . ' continued');

        return $result;
    }

    /**
     * Continue licence part for a PSV
     *
     * @param Licence            $licence            Licence
     * @param ContinuationDetail $continuationDetail ContinuationDetail
     * @param Result             $result             Result to add message to
     *
     * @return void
     */
    private function processPsv(Licence $licence, ContinuationDetail $continuationDetail, Result $result)
    {
        // don't do Special Restricted
        if ($licence->isSpecialRestricted()) {
            return;
        }

        // Update the vehicle authorisation to the value entered
        $licence->updateTotAuthHgvVehicles($continuationDetail->getTotAuthVehicles());

        // Void any discs
        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Discs\CeasePsvDiscs::create(['licence' => $licence->getId()])
            )
        );

        // Create 'X' new PSV discs where X is the number of discs requested
        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs::create(
                    ['licence' => $licence->getId(), 'amount' => $continuationDetail->getTotPsvDiscs()]
                )
            )
        );

        // If licence type is Restricted or Standard International
        if ($licence->isRestricted() || $licence->isStandardInternational()) {
            //Void all community licences
            $result->merge(
                $this->handleSideEffect(
                    \Dvsa\Olcs\Api\Domain\Command\Licence\VoidAllCommunityLicences::create(['id' => $licence->getId()])
                )
            );

            // Generate 'X' new Community licences where X is the number of community licences requested
            // plus the office copy
            $result->merge(
                $this->handleSideEffect(
                    CreateQueueCmd::create(
                        [
                            'type' => QueueEntity::TYPE_CREATE_COM_LIC,
                            'status' => QueueEntity::STATUS_QUEUED,
                            'options' => json_encode(
                                [
                                    'licence' => $licence->getId(),
                                    'totalLicences' => $continuationDetail->getTotCommunityLicences()
                                ]
                            ),
                        ]
                    )
                )
            );
        }
    }

    /**
     * Continue licence part for a Goods
     *
     * @param Licence            $licence            Licence
     * @param ContinuationDetail $continuationDetail ContinuationDetail
     * @param Result             $result             Result to add message to
     *
     * @return void
     */
    private function processGoods(Licence $licence, ContinuationDetail $continuationDetail, Result $result)
    {
        //Void any discs
        $result->merge($this->handleSideEffect(CeaseGoodsDiscs::create(['licence' => $licence->getId()])));

        $count = $this->getRepo('GoodsDisc')->createDiscsForLicence($licence->getId());
        $result->addMessage("{$count} goods discs created");

        //If licence type is Standard International
        if ($licence->isStandardInternational()) {
            //Void all community licences
            $result->merge(
                $this->handleSideEffect(
                    \Dvsa\Olcs\Api\Domain\Command\Licence\VoidAllCommunityLicences::create(['id' => $licence->getId()])
                )
            );

            //Generate 'X' new Community licences where X is the number of community licences requested
            // plus the office copy
            $result->merge(
                $this->handleSideEffect(
                    CreateQueueCmd::create(
                        [
                            'type' => QueueEntity::TYPE_CREATE_COM_LIC,
                            'status' => QueueEntity::STATUS_QUEUED,
                            'options' => json_encode(
                                [
                                    'licence' => $licence->getId(),
                                    'totalLicences' => $continuationDetail->getTotCommunityLicences()
                                ]
                            ),
                        ]
                    )
                )
            );
        }
    }

    /**
     * Create task for signature
     *
     * @param ContinuationDetail $continuationDetail continuation details
     *
     * @return Result
     */
    protected function createTaskForSignature(ContinuationDetail $continuationDetail)
    {
        $sigType = $continuationDetail->getSignatureType();
        if ($sigType !== null && $sigType->getId() === RefData::SIG_DIGITAL_SIGNATURE) {
            $description = Task::TASK_DESCRIPTION_CHECK_DIGITAL_SIGNATURE;
            $actionDate = new DateTime();
        } else {
            $description = Task::TASK_DESCRIPTION_CHECK_WET_SIGNATURE;
            $actionDate = new DateTime('+14 days');
        }

        $createTaskCmd = CreateTaskCmd::create(
            [
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS,
                'description' => $description,
                'actionDate' => $actionDate->format('Y-m-d'),
                'licence' => $continuationDetail->getLicence()->getId()
            ]
        );

        return $this->handleSideEffect($createTaskCmd);
    }

    /**
     * Create task if finances are insufficient
     *
     * @param ContinuationDetail $continuationDetail Continuation details
     * @param Result             $result             Current results object, if task is created then this is modified
     *
     * @return void
     */
    protected function createTaskForInsufficientFinances(ContinuationDetail $continuationDetail, Result $result)
    {
        if ($continuationDetail->getLicence()->isSpecialRestricted()) {
            return;
        }

        if ($continuationDetail->getAmountDeclared() < $this->getAmountRequired($continuationDetail)) {
            $createTaskCmd = CreateTaskCmd::create(
                [
                    'category' => Category::CATEGORY_LICENSING,
                    'subCategory' => Category::TASK_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS,
                    'description' => 'Insufficient finances at continuation',
                    'actionDate' => (new DateTime())->format('Y-m-d'),
                    'licence' => $continuationDetail->getLicence()->getId()
                ]
            );

            $result->merge($this->handleSideEffect($createTaskCmd));
        }
    }

    /**
     * Create task if continuation has other finances
     *
     * @param ContinuationDetail $continuationDetail Continuation details
     * @param Result             $result             Current results object, if task is created then this is modified
     *
     * @return void
     */
    protected function createTaskForOtherFinances(ContinuationDetail $continuationDetail, Result $result)
    {
        // if does not have other finainces then return
        if ((float)$continuationDetail->getOtherFinancesAmount() == 0) {
            return;
        }

        if ($continuationDetail->getAmountDeclared() >= $this->getAmountRequired($continuationDetail)) {
            $createTaskCmd = CreateTaskCmd::create(
                [
                    'category' => Category::CATEGORY_LICENSING,
                    'subCategory' => Category::TASK_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS,
                    'description' => 'Other finances entered at continuation',
                    'actionDate' => (new DateTime())->format('Y-m-d'),
                    'licence' => $continuationDetail->getLicence()->getId()
                ]
            );

            $result->merge($this->handleSideEffect($createTaskCmd));
        }
    }

    /**
     * Get the amount of finance required for a continuation
     *
     * @param ContinuationDetail $continuationDetail Continuation detail
     *
     * @return float
     */
    private function getAmountRequired(ContinuationDetail $continuationDetail)
    {
        return (float)$this->financialStandingHelper->getFinanceCalculationForOrganisation(
            $continuationDetail->getLicence()->getOrganisation()->getId()
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->financialStandingHelper = $container->get('FinancialStandingHelperService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
