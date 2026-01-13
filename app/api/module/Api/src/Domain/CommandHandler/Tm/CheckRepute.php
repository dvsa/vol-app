<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\Command\Tm\CheckReputeProcessDocument as CheckReputeProcessDocumentCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Service\Nr\CheckGoodRepute;
use Dvsa\Olcs\Api\Service\Nr\InrClient;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Transfer\Command\Tm\CheckRepute as CheckReputeCmd;

class CheckRepute extends AbstractCommandHandler
{
    use CheckReputeTrait;

    public const MSG_SUCCESS = 'Repute check response received from INR: %s';
    public const DESC_XML_RESPONSE = 'Repute check (XML): %s';
    public const ERR_CREATING_REQUEST = 'Repute check: error creating request %s';
    public const ERR_SENDING_REQUEST = 'Repute check: error sending request %s';
    public const ERR_RESPONSE_CODE = 'Repute check: error sending request %s';

    protected $repoServiceName = 'TransportManager';

    public function __construct(
        private readonly InrClient $inrClient,
        private readonly CheckGoodRepute $checkGoodReputeService
    ) {
    }

    public function handleCommand(CommandInterface|CheckReputeCmd $command)
    {
        /* @var $repo TransportManagerRepo */
        $repo = $this->getRepo();

        /* @var $transportManager TransportManagerEntity */
        $tmId = (int)$command->getId();
        $transportManager = $repo->fetchById($tmId);
        $tmName = $transportManager->getFullName();

        try {
            $xmlRequest = $this->checkGoodReputeService->create($transportManager);
        } catch (ForbiddenException $e) {
            $this->logErrorCreateFailureTask($tmId, $tmName, sprintf(self::ERR_CREATING_REQUEST, $e->getMessage()));
            return $this->result;
        }

        try {
            $responseXml = $this->inrClient->makeRequestReturnResponse($xmlRequest);
        } catch (\Exception $e) {
            $this->logErrorCreateFailureTask($tmId, $tmName, sprintf(self::ERR_SENDING_REQUEST, $e->getMessage()));
            return $this->result;
        }

        $statusCode = $this->inrClient->getLastStatusCode();

        if ($statusCode !== 200) {
            $this->logErrorCreateFailureTask($tmId, $tmName, sprintf(self::ERR_RESPONSE_CODE, $statusCode));
            return $this->result;
        }

        $this->inrClient->close();

        $this->result->addMessage(sprintf(self::MSG_SUCCESS, $tmName));
        $this->result->addId('Transport Manager', $tmId);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createDocumentCommand($tmId, $tmName, $responseXml)
            )
        );

        $this->result->merge(
            $this->handleSideEffect(
                CheckReputeProcessDocumentCmd::create(['id' => $this->result->getId('document')])
            )
        );

        return $this->result;
    }

    private function createDocumentCommand(int $tmId, string $tmName, string $content): UploadCmd
    {
        $data = [
            'content' => base64_encode(trim($content)),
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK,
            'filename' => 'cgr-response.xml',
            'description' => sprintf(self::DESC_XML_RESPONSE, $tmName),
            'transportManager' => $tmId,
        ];

        return UploadCmd::create($data);
    }
}
