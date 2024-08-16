<?php

/**
 * Print PSV discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PsvDisc;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc;
use Dvsa\Olcs\Api\Entity\System\DiscSequence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc as PsvDiscEntity;
use Dvsa\Olcs\Api\Entity\System\DiscSequence as DiscSequenceEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Print PSV discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrintDiscs extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'PsvDisc';

    protected $extraRepos = ['DiscSequence'];

    /**
     * @param CommandInterface|\Dvsa\Olcs\Transfer\Command\PsvDisc\PrintDiscs $command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var PsvDisc $psvDiscRepository */
        $psvDiscRepository = $this->getRepo();
        $discsToPrint = $psvDiscRepository->fetchDiscsToPrint(
            $command->getLicenceType(),
            $command->getMaxPages() ? $command->getMaxPages() * DiscSequence::DISCS_ON_PAGE : null
        );
        $discIds = array_column($discsToPrint, 'id');
        $this->validateParameters(
            $command->getStartNumber(),
            $discsToPrint,
            $command->getLicenceType(),
            $command->getDiscSequence()
        );

        $data = [
            'discs' => $discIds,
            'type' => 'PSV',
            'startNumber' => $command->getStartNumber(),
            'user' => $this->getCurrentUser()->getId()
        ];
        $params = [
            'type' => Queue::TYPE_DISC_PRINTING,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($data)
        ];
        $printDiscsResult = $this->handleSideEffect(CreatQueue::create($params));
        $result->merge($printDiscsResult);
        $result->addMessage("PSV discs printed");

        $this->createVehicleLists($discsToPrint);

        return $result;
    }

    protected function createVehicleLists($discsToPrint)
    {
        $queries = [];
        $bookmarks = [];
        foreach ($discsToPrint as $disc) {
            $licenceId = $disc['licence']['id'];
            if (!isset($bookmarks[$licenceId]['NO_DISCS_PRINTED'])) {
                $bookmarks[$licenceId] = [
                    'NO_DISCS_PRINTED' => ['count' => 0]
                ];
            }
            $bookmarks[$licenceId]['NO_DISCS_PRINTED']['count']++;
            $queries[$licenceId] = [
                'id' => $licenceId
            ];
        }
        $options = [
            'bookmarks' => $bookmarks,
            'queries' => $queries,
            'user' => $this->getCurrentUser()->getId()
        ];
        $params = [
            'type' => Queue::TYPE_CREATE_PSV_VEHICLE_LIST,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $result = $this->handleSideEffect(CreatQueue::create($params));
        return $result;
    }

    protected function validateParameters($startNumberEntered, $discsToPrint, $licenceType, $discSequence)
    {
        if (!$discsToPrint) {
            throw new ValidationException([PsvDiscEntity::ERROR_NO_DISCS_TO_PRINT => 'No discs to print']);
        }
        $discSequence = $this->getRepo('DiscSequence')->fetchById($discSequence);
        $startNumber = $discSequence->getDiscNumber($licenceType);
        if ($startNumberEntered < $startNumber) {
            throw new ValidationException(
                [
                    'startNumber' => [
                        DiscSequenceEntity::ERROR_DECREASING => 'Decreasing the start number is not permitted'
                    ]
                ]
            );
        }
    }
}
