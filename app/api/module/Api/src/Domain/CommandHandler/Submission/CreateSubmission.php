<?php

/**
 * Create Submission
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmission as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareTrait;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareInterface;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as SectionCommentCommand;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TmApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TmLicenceRepo;

/**
 * Create Submission
 */
final class CreateSubmission extends AbstractCommandHandler implements SubmissionGeneratorAwareInterface, AuthAwareInterface
{
    use SubmissionGeneratorAwareTrait;
    use AuthAwareTrait;

    protected $repoServiceName = 'Submission';

    protected $extraRepos = ['TransportManagerApplication','TransportManagerLicence'];

    /**
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var SubmissionEntity $submissionEntity */
        $submissionEntity = $this->createSubmission($command);
        $repos = [
            TmLicenceRepo::class => $this->getRepo('TransportManagerLicence'),
            TmApplicationRepo::class => $this->getRepo('TransportManagerApplication')
        ];
        $submissionEntity = $this->getSubmissionGenerator()->generateSubmission(
            $submissionEntity,
            $command->getSections(),
            $repos
        );

        $this->getRepo()->save($submissionEntity);

        // add default comments for the submission
        $commentCommands = $this->generateCommentCommands($submissionEntity);

        $this->handleSideEffects($commentCommands);

        $result = new Result();
        $result->addId('submission', $submissionEntity->getId());
        $result->addMessage('Submission created successfully');

        return $result;
    }

    /**
     *
     * @return SubmissionEntity
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createSubmission(Cmd $command)
    {
        $submissionEntity = new SubmissionEntity(
            $this->getRepo()->getReference(CasesEntity::class, $command->getCase()),
            $this->getRepo()->getRefdataReference($command->getSubmissionType())
        );

        $currentUser = $this->getCurrentUser();
        $submissionEntity->setAssignedDate(new \DateTime('now'));
        $submissionEntity->setSenderUser($currentUser);
        $submissionEntity->setRecipientUser($currentUser);

        return $submissionEntity;
    }

    /**
     * Returns an array of Comment commands set up with comment text generated from the section data.
     * Generate comments for all sections that are configured as type = 'text'
     *
     *
     * @return array
     */
    private function generateCommentCommands(SubmissionEntity $submissionEntity)
    {
        $commentCommands = [];
        $selectedSectionsData = $submissionEntity->getSectionData();
        $allSectionsConfig = $this->getSubmissionConfig();

        // foreach section chosen
        foreach ($selectedSectionsData as $selectedSectionId => $selectedSectionData) {
            if (!empty($allSectionsConfig[$selectedSectionId])) {
                // get the config for that section
                $sectionConfig = $allSectionsConfig[$selectedSectionId];

                // if section config entry contains 'text', generate comment based on value of text stored against the
                // section
                if (in_array('text', $sectionConfig['section_type']) && isset($selectedSectionData['data']['text'])) {
                    $commentCommands[] = SectionCommentCommand::create(
                        [
                            'id' => '',
                            'submission' => $submissionEntity->getId(),
                            'submissionSection' => $selectedSectionId,
                            'comment' => $selectedSectionData['data']['text'],
                        ]
                    );
                }
            }
        }

        return $commentCommands;
    }
}
