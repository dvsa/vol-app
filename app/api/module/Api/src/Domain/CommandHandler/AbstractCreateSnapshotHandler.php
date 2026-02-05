<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\LicenceProviderInterface;
use Dvsa\Olcs\Snapshot\Service\Snapshots\SnapshotGeneratorInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;

/**
 * Class AbstractCreateSnapshotHandler
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractCreateSnapshotHandler extends AbstractCommandHandler
{
    protected $repoServiceName = 'changeMe';
    protected $generatorClass = 'changeMe';
    protected $documentCategory = 'changeMe';
    protected $documentSubCategory = 'changeMe';
    protected $documentDescription = 'changeMe';
    protected $documentLinkId = 'changeMe';

    /**
     * @var SnapshotGeneratorInterface
     */
    protected $snapshotService;

    /**
     * handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var RepositoryInterface $repo */
        $repo = $this->getRepo();

        $entity = $repo->fetchUsingId($command);
        $this->snapshotService->setData(['entity' => $entity]);
        $snapshot = $this->snapshotService->generate();

        $this->result->addId($this->repoServiceName, $entity->getId());
        $this->result->addMessage($this->repoServiceName . ' snapshot generated');
        $this->result->merge(
            $this->handleSideEffect($this->getUploadCmd($snapshot, $entity))
        );

        return $this->result;
    }

    /**
     * Get the description of the html snapshot
     *
     * @param mixed $entity the entity where a snapshot is being taken
     *
     * @return string
     */
    protected function getDocumentDescription(mixed $entity): string
    {
        return $this->documentDescription;
    }

    /**
     * Get the upload command
     *
     * @param string $snapshot html content
     * @param mixed  $entity   snapshot was taken from this entity
     *
     * @return Upload
     */
    private function getUploadCmd(string $snapshot, mixed $entity): Upload
    {
        $documentDescription = $this->getDocumentDescription($entity);

        $data = [
            'content' => base64_encode(trim($snapshot)),
            'filename' => str_replace(' ', '', $documentDescription) . '.html',
            'description' => $documentDescription,
            'category' => $this->documentCategory,
            'subCategory' => $this->documentSubCategory,
            'isExternal' => false,
            'isScan' => false,
            'licence' => $entity instanceof LicenceProviderInterface ? $entity->getRelatedLicence() : null,
            $this->documentLinkId => $entity->getId(),
        ];

        return Upload::create($data);
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->snapshotService = $container->get($this->generatorClass);
        return parent::__invoke($container, $requestedName, $options);
    }
}
