<?php

/**
 * Create Vehicle List Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Entity;

/**
 * Create Vehicle List Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateVehicleListDocument extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Licence';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument $command Command to handle
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Entity\Licence\Licence $licence */
        $licence = $this->getRepo()->fetchById($command->getId());

        if ($command->getType() === 'dp') {
            $template = $licence->isNi() ?
                Entity\Doc\Document::GV_DISC_LETTER_NI :
                Entity\Doc\Document::GV_DISC_LETTER_GB;
            $description = 'New disc notification';
        } else {
            $template = $licence->isNi() ?
                Entity\Doc\Document::GV_VEHICLE_LIST_NI :
                Entity\Doc\Document::GV_VEHICLE_LIST_GB;
            $description = 'Goods Vehicle List';
        }

        $user = $command->getUser() ?: $this->getCurrentUser()->getId();
        $documentId = $this->generateDocument($template, $command, $description, $user);

        $printData = [
            'documentId' => $documentId,
            'jobName' => $description,
            'user' => $user,
            'isDiscPrinting' => true,
        ];
        $this->result->merge($this->handleSideEffect(Enqueue::create($printData)));

        return $this->result;
    }

    protected function generateDocument($template, $command, $description, $user)
    {
        $dtoData = [
            'template' => $template,
            'query' => [
                'licence' => $command->getId(),
                'user' => $user
            ],
            'licence'       => $command->getId(),
            'description'   => $description,
            'category'      => Category::CATEGORY_LICENSING,
            'subCategory'   => Category::DOC_SUB_CATEGORY_LICENCE_VEHICLE_LIST,
            'isExternal'    => false,
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $this->result->merge($result);

        return $result->getId('document');
    }
}
