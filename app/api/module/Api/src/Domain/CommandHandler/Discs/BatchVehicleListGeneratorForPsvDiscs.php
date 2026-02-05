<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Discs\CreatePsvVehicleListForDiscs as CreatePsvVehicleListForDiscsCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;

/**
 * Batch Vehicle List Generator for Psv Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class BatchVehicleListGeneratorForPsvDiscs extends AbstractCommandHandler implements
    TransactionedInterface,
    ConfigAwareInterface
{
    use ConfigAwareTrait;

    protected $repoServiceName = 'PsvDisc';

    public const int BATCH_SIZE = 30;

    public function handleCommand(CommandInterface $command)
    {
        $config = $this->getConfig();
        $batchSize = isset($config['disc_printing']['psv_vehicle_list_batch_size'])
            && is_numeric($config['disc_printing']['psv_vehicle_list_batch_size'])
            ? $config['disc_printing']['psv_vehicle_list_batch_size']
            : self::BATCH_SIZE;

        $result = new Result();
        $queries = $command->getQueries();
        $bookmarks = $command->getBookmarks();
        $options = null;

        if (count($queries) > $batchSize) {
            $queuedQueries = array_slice($queries, $batchSize, count($queries) - $batchSize, true);
            $queries = array_slice($queries, 0, $batchSize, true);
            $options = [
                'queries' => $queuedQueries,
                'bookmarks' => $bookmarks,
                'user' => $command->getUser()
            ];
        }

        foreach ($queries as $licenceId => $data) {
            $data['knownValues'] = $bookmarks[$licenceId];
            $data['user'] = $command->getUser();
            $generateVehicleList = CreatePsvVehicleListForDiscsCommand::create($data);
            $this->handleSideEffect($generateVehicleList);
            $result->addMessage('Vehicle list generated for licence ' . $licenceId);
        }

        if ($options) {
            $params = [
                'type' => Queue::TYPE_CREATE_PSV_VEHICLE_LIST,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode($options)
            ];
            $this->handleSideEffect(CreatQueue::create($params));
        }

        return $result;
    }
}
