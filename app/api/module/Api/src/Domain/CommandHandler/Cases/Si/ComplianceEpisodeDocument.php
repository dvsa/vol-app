<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\ComplianceEpisode as ComplianceEpisodeDocCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\ComplianceEpisode as ComplianceEpisodeProcessCmd;

/**
 * Save Si compliance document and then trigger processing
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class ComplianceEpisodeDocument extends AbstractCommandHandler
{
    /**
     * Handle command to save the xml to the doc store, then trigger processing of the compliance episode
     *
     * @param CommandInterface|ComplianceEpisodeDocCmd $command the command
     *
     * @return Result
     * @throws Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var array $erruData
         */
        $result = $this->handleSideEffect(
            $this->createDocumentCommand($command->getXml())
        );

        //compliance episode runs inside a transaction, and needs to save to the database both success and failure
        $result->merge(
            $this->handleSideEffect(
                ComplianceEpisodeProcessCmd::create(['id' => $result->getId('document')])
            )
        );

        //throwing the exception within the compliance episode processor would result in a DB rollback,
        //so we check for errors and throw the exception here instead
        if ($result->getFlag('hasErrors')) {
            throw new Exception('some data was not correct');
        }

        return $result;
    }

    /**
     * Returns an upload command to add xml to the doc store
     *
     * @param string $content document content
     *
     * @return UploadCmd
     */
    private function createDocumentCommand($content)
    {
        $data = [
            'content' => base64_encode($content),
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_NR,
            'filename' => 'compliance-episode.xml',
            'description' => 'ERRU incoming compliance episode',
            'isExternal' => true
        ];

        return UploadCmd::create($data);
    }
}
