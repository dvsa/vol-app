<?php

/**
 * Print goods discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GoodsDisc;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc;
use Dvsa\Olcs\Api\Entity\System\DiscSequence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc as GoodsDiscEntity;
use Dvsa\Olcs\Api\Entity\System\DiscSequence as DiscSequenceEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Print goods discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrintDiscs extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'GoodsDisc';

    protected $extraRepos = ['DiscSequence'];

    /**
     * @param CommandInterface|\Dvsa\Olcs\Transfer\Command\GoodsDisc\PrintDiscs $command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var GoodsDisc $goodsDiscRepository */
        $goodsDiscRepository = $this->getRepo();
        $discsToPrint = $goodsDiscRepository->fetchDiscsToPrint(
            $command->getNiFlag(),
            $command->getLicenceType(),
            $command->getMaxPages() ? $command->getMaxPages() * DiscSequence::DISCS_ON_PAGE : null
        );

        $this->validateParameters(
            $command->getStartNumber(),
            $discsToPrint,
            $command->getLicenceType(),
            $command->getDiscSequence()
        );

        $data = [
            'discs' => array_column($discsToPrint, 'id'),
            'type' => 'Goods',
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
        $result->addMessage("Goods discs printed");

        $this->createVehicleLists($discsToPrint);

        return $result;
    }

    protected function createVehicleLists($discsToPrint)
    {
        $licences = [];
        foreach ($discsToPrint as $disc) {
            $licenceId = $disc['licenceVehicle']['licence']['id'];
            $licences[$licenceId] = [
                'id' => $licenceId,
                'type' => 'dp'
            ];
        }
        $options = [
            'licences' => $licences,
            'user' => $this->getCurrentUser()->getId()
        ];
        $params = [
            'type' => Queue::TYPE_CREATE_GOODS_VEHICLE_LIST,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $result = $this->handleSideEffect(CreatQueue::create($params));

        return $result;
    }

    protected function validateParameters($startNumberEntered, $discsToPrint, $licenceType, $discSequence)
    {
        if (!$discsToPrint) {
            throw new ValidationException([GoodsDiscEntity::ERROR_NO_DISCS_TO_PRINT => 'No discs to print']);
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
