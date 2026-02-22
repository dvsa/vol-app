<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Generator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Psr\Container\ContainerInterface;

class Snapshot extends AbstractSurrenderCommandHandler implements TransactionedInterface
{
    /**
     * @var Generator
     */
    protected $snapshotService;

    public function handleCommand(CommandInterface $command)
    {
        /** @var $surrender \Dvsa\Olcs\Api\Entity\Surrender */
        $surrender = $this->getRepo()->fetchOneByLicenceId($command->getId(), Query::HYDRATE_OBJECT);

        $snapshot = $this->snapshotService->generate($surrender);

        $this->result->addMessage('Snapshot generated');

        $this->result->merge($this->uploadSnapshot($snapshot, $command->getId(), $surrender->getId()));

        return $this->result;
    }

    private function uploadSnapshot($snapshot, $licId, $surrenderId)
    {

        $data = [
            'content' => base64_encode(trim((string) $snapshot)),
            'filename' => 'Surrender Snapshot.html',
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
            'isExternal' => false,
            'isScan' => false,
            'licence' => $licId,
            'surrender' => $surrenderId,
        ];

        return $this->handleSideEffect(Upload::create($data));
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->snapshotService = $container->get(Generator::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
