<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateCoverLetter as GenerateCoverLetterCmd;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Generate Cover Letter
 */
final class GenerateCoverLetter extends AbstractCommandHandler
{
    protected $repoServiceName = 'Licence';

    /**
     * Handle Command
     *
     * @param GenerateCoverLetterCmd $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo('Licence')->fetchById($command->getLicence());

        if ($licence->isPsv()) {
            $this->result->addMessage('UK licence for the Community cover letter not required for PSV');

            return $this->result;
        }

        // get template
        $template = $this->getTemplate($licence);

        $query = [
            'licence' => $licence->getId(),
        ];

        // generate document
        $docId = $this->generateDocument($template, $query);

        // print
        $this->result->merge(
            $this->handleSideEffect(
                EnqueueFileCommand::create(
                    [
                        'documentId' => $docId,
                        'jobName' => 'UK licence for the Community cover letter',
                        'user' => $command->getUser(),
                    ]
                )
            )
        );

        $this->result->addMessage('UK licence for the Community cover letter processed');

        return $this->result;
    }

    /**
     * Get the template
     *
     * @param Licence $licence Licence entity
     *
     * @return string
     */
    private function getTemplate(Licence $licence): string
    {
        return $licence->isNi()
            ? Document::GV_UK_COMMUNITY_LICENCE_NI_COVER_LETTER
            : Document::GV_UK_COMMUNITY_LICENCE_GB_COVER_LETTER;
    }

    /**
     * Generate document
     *
     * @param string $template Template
     * @param array  $query    Query
     *
     * @return int
     */
    private function generateDocument($template, array $query): int
    {
        $result = $this->handleSideEffect(
            GenerateAndStore::create(
                [
                    'template' => $template,
                    'query' => $query,
                    'description' => 'UK licence for the Community cover letter',
                    'category' => Category::CATEGORY_LICENSING,
                    'subCategory' => SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE_COVER,
                    'isExternal' => false,
                    'isScan' => false
                ]
            )
        );

        $this->result->merge($result);

        return $result->getId('document');
    }
}
