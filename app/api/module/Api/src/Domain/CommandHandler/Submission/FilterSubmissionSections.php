<?php

/**
 * Filter Submission
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Transfer\Command\Submission\FilterSubmissionSections as Cmd;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareTrait;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareInterface;

/**
 * Filter Submission
 */
final class FilterSubmissionSections extends AbstractCommandHandler implements SubmissionGeneratorAwareInterface
{
    use SubmissionGeneratorAwareTrait;

    protected $repoServiceName = 'Submission';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Submission $submission */
        $submissionEntity = $this->filterSubmission($command);

        $this->getRepo()->save($submissionEntity);

        $result = new Result();
        $result->addId('submission', $submissionEntity->getId());
        $result->addMessage('Submission filtered successfully');

        return $result;
    }

    /**
     * @return Submission
     */
    private function filterSubmission(Cmd $command)
    {
        /** @var Submission $submissionEntity */
        $submissionEntity = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // set section data prior to update in order to retain any other sections
        $dataSnapshot = json_decode($submissionEntity->getDataSnapshot(), true);

        $sectionToFilter = !empty($command->getSubSection()) ? $command->getSubSection() : $command->getSection();

        $dataSnapshot[$command->getSection()]['data']['tables'][$sectionToFilter] =
            $this->filterTable(
                $dataSnapshot[$command->getSection()]['data']['tables'][$sectionToFilter],
                $command->getRowsToFilter()
            );

        $submissionEntity->setNewSubmissionDataSnapshot($dataSnapshot);

        return $submissionEntity;
    }

    private function filterTable($table, $rowsToRemove)
    {
        foreach ($table as $key => $dataRow) {
            if (in_array($dataRow['id'], $rowsToRemove)) {
                unset($table[$key]);
            }
        }
        ksort($table);

        return $table;
    }
}
