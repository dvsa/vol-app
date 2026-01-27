<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\Command\Tm\CheckReputeProcessDocument as CheckReputeProcessDocumentCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Nr\Mapping\CgrResponseXml;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck\Generator as TmReputeCheckGenerator;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Olcs\Logging\Log\Logger;

class CheckReputeProcessDocument extends AbstractCommandHandler
{
    use CheckReputeTrait;

    public const MSG_SUCCESS = 'Repute check snapshot saved: %s';
    public const DESC_SNAPSHOT = 'Repute check (HTML): %s';
    public const ERR_UNABLE_TO_READ_XML = 'Repute check snapshot creation failed: unable to read XML file';
    public const ERR_XML_NOT_VALID = 'Repute check snapshot creation failed: XML was not valid';
    public const ERR_SNAPSHOT_FAILED = 'Repute check snapshot creation failed: %s';

    protected $repoServiceName = 'Document';

    public function __construct(
        private readonly ContentStoreFileUploader $fileUploader,
        private readonly Input $cgrInputFilter,
        private readonly CgrResponseXml $cgrXmlMapping,
        private readonly TmReputeCheckGenerator $snapshotGenerator
    ) {
    }

    public function handleCommand(CommandInterface|CheckReputeProcessDocumentCmd $command)
    {
        /* @var $repo DocumentRepo */
        $repo = $this->getRepo();
        $document = $repo->fetchById($command->getId());

        $transportManager = $document->getTransportManager();
        $tmId = (int)$transportManager->getId();
        $tmName = $transportManager->getFullName();

        $xmlFile = $this->fileUploader->download($document->getIdentifier());

        if (!$xmlFile instanceof File) {
            $this->logErrorCreateFailureTask($tmId, $tmName, self::ERR_UNABLE_TO_READ_XML);
            return $this->result;
        }

        $xmlContent = $xmlFile->getContent();
        $this->cgrInputFilter->setValue($xmlContent);

        if (!$this->cgrInputFilter->isValid()) {
            $this->logErrorCreateFailureTask($tmId, $tmName, self::ERR_XML_NOT_VALID, $this->cgrInputFilter->getMessages());
            return $this->result;
        }

        /** @var \DOMDocument $domDocument */
        $domDocument = $this->cgrInputFilter->getValue();
        $snapshotData = $this->cgrXmlMapping->mapData($domDocument);

        try {
            $snapshotHtml = $this->snapshotGenerator->generate($snapshotData);
        } catch (\Exception $e) {
            $this->logErrorCreateFailureTask($tmId, $tmName, sprintf(self::ERR_SNAPSHOT_FAILED, $e->getMessage()));
            return $this->result;
        }

        $this->result->merge(
            $this->handleSideEffect(
                $this->uploadSnapshotCommand($tmId, $tmName, $snapshotHtml)
            )
        );

        $this->result->addMessage(sprintf(self::MSG_SUCCESS, $tmName));
        return $this->result;
    }

    private function uploadSnapshotCommand(int $tmId, string $tmName, string $snapshotHtml): UploadCmd
    {
        $data = [
            'content' => base64_encode(trim($snapshotHtml)),
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK,
            'filename' => 'cgr-snapshot.html',
            'description' => sprintf(self::DESC_SNAPSHOT, $tmName),
            'transportManager' => $tmId,
        ];

        return UploadCmd::create($data);
    }
}
