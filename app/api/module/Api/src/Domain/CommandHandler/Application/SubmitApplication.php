<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\AutoGrant;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateLightGoodsVehicleCondition
    as CreateLightGoodsVehicleConditionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareInterface;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareTrait;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot as CreateSnapshotCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Submit Application
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class SubmitApplication extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface, SlaCalculatorAwareInterface, CacheAwareInterface
{
    use AuthAwareTrait;
    use SlaCalculatorAwareTrait;
    use CacheAwareTrait;

    protected $repoServiceName = 'Application';
    protected $extraRepos = ['TransportManagerApplication', 'Sla', 'Organisation'];

    /**
     * Handle Command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Application\SubmitApplication $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command) : Result
    {
        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->validate($application);

        $this->result->merge($this->snapshotApplication($application));

        $this->updateStatus($application);

        if ($application->canAutoGrant()) {
            $this->result->merge(
                $this->handleSideEffectAsSystemUser(
                    AutoGrant::create([
                        'id' => $application->getId(),
                    ])
                )
            );
        }

        try {
            $this->clearLicenceCaches($application->getLicence());
        } catch (\Exception $e) {
            Logger::err(
                'Cache clear by licence failed when submitting application',
                [
                    'application_id' => $application->getId(),
                    'licence_id' => $application->getLicence()->getId(),
                    'exception' => [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ]
            );
        }

        $this->result->merge($this->createTask($application));
        $this->result->merge($this->maybeCreateLightGoodsVehicleCondition($application));

        if ($this->shouldPublishApplication($application)) {
            $this->result->merge($this->createPublication($application));
            $this->result->merge($this->createTexTask($application));
        }

        return $this->result;
    }

    /**
     * Should the application be published
     *
     * @param ApplicationEntity $application Application
     *
     * @return boolean
     */
    private function shouldPublishApplication(ApplicationEntity $application)
    {
        if ($this->isInternalUser()) {
            return false;
        }

        // Exclude for PSV variation applications
        if ($application->isVariation() && $application->isPsv()) {
            return false;
        }

        // Dont publish if application is associated with an S4 whoose outcome is empty or approved
        if ($application->hasActiveS4()) {
            return false;
        }

        return $application->isPublishable();
    }

    /**
     * Update the application and licence status (if applicable)
     *
     * @param ApplicationEntity $application Appication
     *
     * @return void
     */
    private function updateStatus(ApplicationEntity $application)
    {
        $now = new DateTime();

        $newStatus = ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION;
        $status = $this->getRepo()->getRefdataReference($newStatus);
        $licence = null;

        $application
            ->setStatus($status)
            ->setReceivedDate($now);

        $this->setTargetCompletionDateForApplication($application);

        if (!$application->isVariation()) {
            // update licence status for new apps only, will cascade persist on save
            $licence = $application->getLicence();
            $licence->setStatus(
                $this->getRepo()->getRefdataReference(
                    LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION
                )
            );
        }

        $this->getRepo()->save($application);

        $this->result
            ->addId('application', $application->getId())
            ->addMessage('Application updated');

        if ($licence !== null) {
            $this->result
                ->addId('licence', $licence->getId())
                ->addMessage('Licence updated');
        }
    }

    /**
     * Snapshot Application
     *
     * @param ApplicationEntity $application Applicaiton Entity
     *
     * @return Result
     */
    private function snapshotApplication(ApplicationEntity $application)
    {
        $data = [
            'id' => $application->getId(),
            'event' => CreateSnapshotCmd::ON_SUBMIT
        ];
        return $this->handleSideEffect(CreateSnapshotCmd::create($data));
    }

    /**
     * Create task
     *
     * @param ApplicationEntity $application Application Entity
     *
     * @return Result
     */
    private function createTask(ApplicationEntity $application)
    {
        $now = new \DateTime();
        $actionDate = $now->format('Y-m-d');

        $taskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => $this->getTaskDescription($application),
            'actionDate' => $actionDate,
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId(),
        ];

        if ($application->isVariation()) {
            $taskData = $this->getVariationTaskdata($application, $taskData);
        }

        return $this->handleSideEffect(CreateTaskCmd::create($taskData));
    }

    /**
     * Modify the task creation data for a variation
     *
     * @param ApplicationEntity $application Application entity
     * @param array             $taskData    Task data
     *
     * @return array
     */
    private function getVariationTaskdata(ApplicationEntity $application, array $taskData)
    {
        // If People is only change section
        if ($this->isOnlyCompletedSection($application, ApplicationCompletion::SECTION_PEOPLE)) {
            // If Ltd company
            if ($application->getLicence()->getOrganisation()->isLtd()) {
                $taskData['subCategory'] = CategoryEntity::TASK_SUB_CATEGORY_DIRECTOR_CHANGE_DIGITAL;
                $taskData['description'] = 'Director change application';
            } else {
                $taskData['subCategory'] = CategoryEntity::TASK_SUB_CATEGORY_PARTNER_CHANGE_DIGITAL;
                $taskData['description'] = 'Partner change application';
            }
            return $taskData;
        }

        if ($application::CODE_GV_VAR_UPGRADE === $application->getCode()) {
            $taskData['urgent'] = 'Y';
        }

        // If Transport manager section changed
        if ($this->isOnlyCompletedSection($application, ApplicationCompletion::SECTION_TRANSPORT_MANAGER)) {
            /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication $repoTma */
            $repoTma = $this->getRepo('TransportManagerApplication');

            $stat = $repoTma->fetchStatByAppId($application->getId());

            //  If was not added, but was removed at least ONE transport manager
            $statByAction = $stat['action'];
            if (
                (int)$statByAction[Entity\Tm\TransportManagerApplication::ACTION_ADD] === 0
                && $statByAction[Entity\Tm\TransportManagerApplication::ACTION_DELETE] > 0
            ) {
                $taskData['subCategory'] = CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_TM1_REMOVAL_VARIATION;
                $taskData['description'] = 'TM1 (Removal only)';

                return $taskData;
            }

            $taskData['subCategory'] = CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_TM1_DIGITAL;
            $taskData['description'] = 'TM change variation';
        }

        return $taskData;
    }

    /**
     * Is a section the only completed section
     *
     * @param ApplicationEntity $application Application entity
     * @param string            $section     Section to test if its the only completed section
     *
     * @return bool
     */
    private function isOnlyCompletedSection(ApplicationEntity $application, $section)
    {
        // These sections are ignored as they always have to be completed
        $ignoredSections = [
            ApplicationCompletion::SECTION_DECLARATION,
            ApplicationCompletion::SECTION_DECLARATION_INTERNAL,
            ApplicationCompletion::SECTION_FINANCIAL_HISTORY,
            ApplicationCompletion::SECTION_LICENCE_HISTORY,
            ApplicationCompletion::SECTION_CONVICTIONS_AND_PENALTIES,
        ];
        $completionStatuses = $application->getVariationCompletion();

        foreach ($completionStatuses as $completionSection => $status) {
            if (in_array($completionSection, $ignoredSections)) {
                // ignore sections
                continue;
            }
            if ($completionSection === $section) {
                if ($status !== \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion::STATUS_COMPLETE) {
                    return false;
                }
            } else {
                if ($status === \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion::STATUS_COMPLETE) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * check, if can be submitted
     *
     * @param ApplicationEntity $application Application Entity
     *
     * @return boolean
     * @throws Exception\ValidationException
     */
    private function validate(ApplicationEntity $application)
    {
        if (!$application->canSubmit()) {
            $msg = sprintf(
                "Cannot submit application with status '%s'",
                $application->getStatus()->getDescription()
            );
            throw new Exception\ValidationException([$msg]);
        }

        return true;
    }

    /**
     * Get Task Description
     *
     * @param ApplicationEntity $application Application Entity
     *
     * @return string
     */
    protected function getTaskDescription(ApplicationEntity $application)
    {
        return $application->getCode() . ' Application';
    }

    /**
     * Create a publicaction
     *
     * @param ApplicationEntity $application Application Entity
     *
     * @return Result
     */
    protected function createPublication(ApplicationEntity $application)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::create(
                [
                    'id' => $application->getId(),
                    'trafficArea' => $application->getTrafficArea()->getId(),
                ]
            )
        );
    }

    /**
     * Create a TEX task
     *
     * @param ApplicationEntity $application Application Entity
     *
     * @return Result
     */
    protected function createTexTask(ApplicationEntity $application)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(
                [
                    'id' => $application->getId(),
                ]
            )
        );
    }

    /**
     * Maybe create light goods vehicle condition
     *
     *
     * @return Result
     */
    protected function maybeCreateLightGoodsVehicleCondition(ApplicationEntity $application)
    {
        return $this->handleSideEffectAsSystemUser(
            CreateLightGoodsVehicleConditionCmd::create(
                ['applicationId' => $application->getId()]
            )
        );
    }
}
