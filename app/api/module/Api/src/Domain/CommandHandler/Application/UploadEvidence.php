<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * UploadEvidence
 */
final class UploadEvidence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOperatingCentre', 'Task'];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command DTO
     *
     * @return Result
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchById($command->getId());
        $applicationCategory = $this->getRepo()->getCategoryReference(Category::CATEGORY_APPLICATION);
        $financialEvidenceSubCategory = $this->getRepo()->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL);

        if ($command->getFinancialEvidence()) {
            $financialEvidenceDocuments = $application->getApplicationDocuments(
                $applicationCategory,
                $financialEvidenceSubCategory
            );

            if (!$financialEvidenceDocuments->isEmpty()) {
                $application->setFinancialEvidenceUploaded(Application::FINANCIAL_EVIDENCE_UPLOADED);
                foreach (
                    $application->getPostSubmissionApplicationDocuments(
                        $applicationCategory,
                        $financialEvidenceSubCategory
                    ) as $postSubmissionApplicationDocument
                ) {
                    $postSubmissionApplicationDocument->setIsPostSubmissionUpload(false);
                }
                $this->getRepo()->save($application);
                $this->createTaskForFinancialEvidence($application);
                $this->result->addMessage('Financial evidence uploaded');
            }
        }

        foreach ($command->getOperatingCentres() as $commandOc) {
            $applicationOperatingCentre = $application->getApplicationOperatingCentreById($commandOc['aocId']);
            if ($applicationOperatingCentre === null) {
                throw new ValidationException(['No operating centre found']);
            }
            $operatingCentreId = $applicationOperatingCentre->getOperatingCentre()->getId();
            $advertDigitalDocuments = $application->getApplicationDocuments(
                $this->getRepo()->getCategoryReference(Category::CATEGORY_APPLICATION),
                $this->getRepo()->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_ADVERT_DIGITAL),
                $this->getRepo()->getReference(OperatingCentre::class, $operatingCentreId)
            );

            if (
                !$advertDigitalDocuments->isEmpty()
                && !empty($commandOc['adPlacedIn'])
                && !empty($commandOc['adPlacedDate'])
            ) {
                $applicationOperatingCentre->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW);
                $this->createTaskForOperatingCentre($application);
                $this->result->addMessage('Advert digital documents for OC ' . $operatingCentreId . ' uploaded');
                foreach ($advertDigitalDocuments as $advertDigitalDocument) {
                    $advertDigitalDocument->setIsPostSubmissionUpload(false);
                }
            }

            $applicationOperatingCentre->setAdPlacedIn($commandOc['adPlacedIn']);
            $applicationOperatingCentre->setAdPlacedDate($commandOc['adPlacedDate']);
            $this->getRepo('ApplicationOperatingCentre')->save($applicationOperatingCentre);
            $this->result->addMessage('Advert details for OC ' . $operatingCentreId . ' saved');
        }

        if ($command->getSupportingEvidence()) {
            $supporingDigitalDocuments = $application->getApplicationDocuments(
                $this->getRepo()->getCategoryReference(Category::CATEGORY_APPLICATION),
                $this->getRepo()->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE)
            );
            if (!$supporingDigitalDocuments->isEmpty()) {
                $this->getRepo()->save($application);
                $this->createTaskForSupportingEvidence($application);
                $this->result->addMessage('Supporting document uploaded');
            }
        }
        return $this->result;
    }

    /**
     * Create task for supporting evidence
     *
     * @param Application $application application
     *
     * @return void
     * @throws RuntimeException
     */
    protected function createTaskForSupportingEvidence(Application $application): void
    {
        $data = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE,
            'description' => Task::TASK_DESCRIPTION_SUPPORTING_EVIDENCE_UPLOADED,
            'actionDate' => (new DateTime('now'))->format(Task::ACTION_DATE_FORMAT),
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId()
        ];

        $this->result->merge($this->handleSideEffect(CreateTask::create($data)));
    }

    /**
     * Create task for financial evidence
     *
     * @param Application $application application
     *
     * @return void
     * @throws RuntimeException
     */
    protected function createTaskForFinancialEvidence($application)
    {
        $existedTasks = $this->getRepo('Task')->fetchByAppIdAndDescription(
            $application->getId(),
            Task::TASK_DESCRIPTION_FINANCIAL_EVIDENCE_UPLOADED
        );
        if (count($existedTasks) > 0) {
            return;
        }
        $data = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'description' => Task::TASK_DESCRIPTION_FINANCIAL_EVIDENCE_UPLOADED,
            'actionDate' => (new DateTime('now'))->format(Task::ACTION_DATE_FORMAT),
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId()
        ];

        $this->result->merge($this->handleSideEffect(CreateTask::create($data)));
    }

    /**
     * Create task for operating centre
     *
     * @param Application $application application
     *
     * @return void
     * @throws RuntimeException
     */
    protected function createTaskForOperatingCentre($application)
    {
        $existedTasks = $this->getRepo('Task')->fetchByAppIdAndDescription(
            $application->getId(),
            Task::TASK_DESCRIPTION_OC_EVIDENCE_UPLOADED
        );
        if (count($existedTasks) > 0) {
            return;
        }
        $data = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_ADVERT_DIGITAL,
            'description' => Task::TASK_DESCRIPTION_OC_EVIDENCE_UPLOADED,
            'actionDate' => (new DateTime('now'))->format(Task::ACTION_DATE_FORMAT),
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId()
        ];

        $this->result->merge($this->handleSideEffect(CreateTask::create($data)));
    }
}
