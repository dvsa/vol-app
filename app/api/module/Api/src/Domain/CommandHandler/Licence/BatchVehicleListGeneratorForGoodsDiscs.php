<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as CreateVehicleListDocumentCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;

/**
 * Batch Vehicle List Generator for Goods Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class BatchVehicleListGeneratorForGoodsDiscs extends AbstractCommandHandler implements
    TransactionedInterface,
    ConfigAwareInterface
{
    use ConfigAwareTrait;

    protected $repoServiceName = 'GoodsDisc';

    public const int BATCH_SIZE = 30;

    public function handleCommand(CommandInterface $command)
    {
        $config = $this->getConfig();
        $batchSize = isset($config['disc_printing']['gv_vehicle_list_batch_size'])
            && is_numeric($config['disc_printing']['gv_vehicle_list_batch_size'])
            ? $config['disc_printing']['gv_vehicle_list_batch_size']
            : self::BATCH_SIZE;

        $result = new Result();
        $licences = $command->getLicences();
        $options = null;

        if (count($licences) > $batchSize) {
            $queuedLicences = array_slice($licences, $batchSize);
            $licences = array_slice($licences, 0, $batchSize);
            $options = [
                'licences' => $queuedLicences,
                'user' => $command->getUser()
            ];
        }
        foreach ($licences as $data) {
            $data['user'] = $command->getUser();
            $generateVehicleList = CreateVehicleListDocumentCommand::create($data);
            $this->handleSideEffect($generateVehicleList);
            $result->addMessage('Vehicle list generated for licence ' . $data['id']);
        }
        if ($options) {
            $params = [
                'type' => Queue::TYPE_CREATE_GOODS_VEHICLE_LIST,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode($options)
            ];
            $this->handleSideEffect(CreatQueue::create($params));
        }

        return $result;
    }
}
