<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Document\UploadCsv;
use Dvsa\Olcs\Api\Domain\Command\Permits\RoadworthinessReport as RoadworthinessReportCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslatorAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Note\Note;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\EntityNotFoundException;

class RoadworthinessReport extends AbstractCommandHandler implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public const MSG_USER_MISSING = 'User details missing';

    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle command
     *
     * @param RoadworthinessReportCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationRepo = $this->getRepo('IrhpApplication');
        assert($irhpApplicationRepo instanceof IrhpApplicationRepo);

        //fetch roadworthiness certificates for the report
        $apps = $irhpApplicationRepo->fetchForRoadworthinessReport($command->getStartDate(), $command->getEndDate());

        //we split the data into different sheets for the various app paths (questions can change in between)
        $includedAppPaths = [];
        $rows = [];

        foreach ($apps as $irhpApplication) {
            assert($irhpApplication instanceof IrhpApplicationEntity);

            $documents = $irhpApplication->getDocumentsByCategoryAndSubCategory(
                Category::CATEGORY_PERMITS,
                SubCategory::DOC_SUB_CATEGORY_PERMIT
            );

            //if there is no document then we skip this record
            if ($documents->isEmpty()) {
                continue;
            }

            $document = $documents->first();
            assert($document instanceof Document);
            $issueDate = $document->getCreatedOn(true)->format('Y-m-d');

            //deal with missing user records for the document
            try {
                $issuedBy = $document->getCreatedBy()->getContactDetails()->getPerson()->getFullName();
            } catch (EntityNotFoundException) {
                $issuedBy = self::MSG_USER_MISSING;
            }

            //deal with missing user records for the irhp application
            try {
                $lastUpdateBy = $irhpApplication->getLastModifiedBy()->getContactDetails()->getPerson()->getFullName();
            } catch (EntityNotFoundException) {
                $lastUpdateBy = self::MSG_USER_MISSING;
            }

            $isTrailer = $irhpApplication->isCertificateOfRoadworthinessTrailer();

            $appNotes = '';
            $notes = $irhpApplication->getNotes();

            if (!$notes->isEmpty()) {
                $noteArray = [];

                foreach ($notes as $note) {
                    assert($note instanceof Note);
                    $noteArray[] = trim($note->getComment());
                }

                $appNotes = implode(' | ', $noteArray);
            }

            //get the Q&A data and unset the check answer and declaration parts
            $questionAnswerData = $irhpApplication->getQuestionAnswerData();
            unset($questionAnswerData['custom-check-answers'], $questionAnswerData['custom-declaration']);

            $newRow = [
                'Certificate no.' => $irhpApplication->getCorCertificateNumber(),
                'Operator name' => $irhpApplication->getRelatedOrganisation()->getName(),
                'Application no.' => $irhpApplication->getApplicationRef(),
                'Certificate type' => $isTrailer ? 'Trailer' : 'Vehicle',
                'Application status' => $irhpApplication->getStatus()->getDescription(),
                'Date received' => $irhpApplication->getDateReceived(),
                'Issue date' => $issueDate,
                'Issued by' => $issuedBy,
                'Last app update by' => $lastUpdateBy,
            ];

            foreach ($questionAnswerData as $answerData) {
                $questionHeading = $this->translate($answerData['questionShort']);
                $newRow[$questionHeading] = $answerData['answer'];
            }

            //notes can be a bit longer than the other fields, so we purposefully put them at the end
            $newRow['Notes'] = $appNotes;

            //work out which application path the app is from, and put it into the correct CSV
            $appPath = $irhpApplication->getActiveApplicationPath();
            $appPathId = $appPath->getId();
            $includedAppPaths[$appPathId] = $appPath->getTitle();

            $rows[$appPathId][] = $newRow;
        }

        $userId = $command->getUser();

        foreach ($includedAppPaths as $id => $title) {
            $this->result->merge(
                $this->handleSideEffect(
                    $this->getUploadCmd($rows[$id], $title, $userId)
                )
            );
        }

        return $this->result;
    }

    /**
     * Get the upload CSV command
     *
     * @param array  $rows            rows going into the CSV
     * @param string $fileDescription description of the report
     * @param int    $userId          id of the user who requested the report
     *
     * @return UploadCsv
     */
    private function getUploadCmd(array $rows, string $fileDescription, int $userId): UploadCsv
    {
        $cmdData = [
            'csvContent' => $rows,
            'fileDescription' => $fileDescription,
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
            'user' => $userId,
        ];

        return UploadCsv::create($cmdData);
    }
}
