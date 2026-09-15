<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Api\Service\Letter\PreviewRecordSuggester;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\SuggestPreviewRecords as Cmd;
use Psr\Container\ContainerInterface;

/**
 * Suggest records that would exercise the composition's specific variants.
 *
 * NOTHING IS WRITTEN. A command only because the unsaved composition travels with
 * the request, exactly as PreviewComposition does.
 */
final class SuggestPreviewRecords extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterType';

    protected $extraRepos = ['LetterSection'];

    private PreviewRecordSuggester $suggester;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->suggester = $container->get(PreviewRecordSuggester::class);

        return parent::__invoke($container, $requestedName, $options);
    }

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var LetterTypeEntity $letterType */
        $letterType = $this->getRepo()->fetchById($command->getLetterType());

        $sections = [];

        if ($command->getSections() !== null) {
            foreach ($command->getSections() as $sectionId) {
                $sections[] = $this->getRepo('LetterSection')->fetchById($sectionId);
            }
        } else {
            foreach ($letterType->getLetterTypeSections() as $typeSection) {
                $sections[] = $typeSection->getLetterSection();
            }
        }

        $this->result->setFlag('suggestions', $this->suggester->suggest($sections));

        return $this->result;
    }
}
