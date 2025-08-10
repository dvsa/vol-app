<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\InrClientException;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Service\Nr\CheckGoodRepute;
use Dvsa\Olcs\Api\Service\Nr\InrClient;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Laminas\Http\Client\Adapter\Exception\RuntimeException as AdapterRuntimeException;

class CheckRepute extends AbstractCommandHandler
{
    protected $repoServiceName = 'TransportManager';

    public function __construct(private readonly InrClient $inrClient, private readonly CheckGoodRepute $checkGoodReputeService)
    {
    }

    public function handleCommand(CommandInterface $command)
    {
        /* @var $repo TransportManagerRepo */
        $repo = $this->getRepo();

        /* @var $transportManager TransportManagerEntity */
        $transportManagerId = $command->getId();
        $transportManager = $repo->fetchById($transportManagerId);

        $xmlRequest = $this->checkGoodReputeService->create($transportManager);

        try {
            $responseXml = $this->inrClient->makeRequestReturnResponse($xmlRequest);
        } catch (AdapterRuntimeException $e) {
            throw new InrClientException('Repute check: error sending the INR response ' . $e->getMessage());
        }

        $this->inrClient->close();

        $this->result->addMessage('Repute check request sent to INR');
        $this->result->addId('Transport Manager', $transportManagerId);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createDocumentCommand($transportManagerId, $responseXml)
            )
        );

        $this->result->getId('document');

        return $this->result;
    }

    private function createDocumentCommand(int $transportManagerId, string $content): UploadCmd
    {
        $data = [
            'content' => base64_encode($content),
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_NR,
            'filename' => 'cgr-response.xml',
            'description' => sprintf('INR response for transport manager ID: %s', $transportManagerId),
            'transportManager' => $transportManagerId,
        ];

        return UploadCmd::create($data);
    }
}
