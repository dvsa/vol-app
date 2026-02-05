<?php

/**
 * Print Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;

/**
 * Print Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrintDiscs extends AbstractCommandHandler implements TransactionedInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;

    public const int BATCH_SIZE = 30;

    protected $repoServiceName = 'GoodsDisc';
    protected $extraRepos = ['PsvDisc'];

    private $params = [
        'PSV' => [
            'template' => 'PSVDiscTemplate',
            'bookmark' => 'Psv_Disc_Page',
            'repo' => 'PsvDisc'
        ],
        'Goods' => [
            'template' => 'GVDiscTemplate',
            'bookmark' => 'Disc_List',
            'repo' => 'GoodsDisc'
        ]
    ];

    /**
     * @param CommandInterface|\Dvsa\Olcs\Api\Domain\Command\Discs\PrintDiscs $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $config = $this->getConfig();
        $batchSize = isset($config['disc_printing']['disc_batch_size'])
            && is_numeric($config['disc_printing']['disc_batch_size'])
            ? $config['disc_printing']['disc_batch_size']
            : self::BATCH_SIZE;

        $bookmark = $this->params[$command->getType()]['bookmark'];
        $options = null;

        $discsToPrintIds = $command->getDiscs();

        if (count($discsToPrintIds) > $batchSize) {
            $queuedDiscsIds = array_slice($discsToPrintIds, $batchSize);
            $discsToPrintIds = array_slice($discsToPrintIds, 0, $batchSize);
            $queuedStartNumber = $command->getStartNumber() + $batchSize;
            $options = [
                'discs' => $queuedDiscsIds,
                'startNumber' => $queuedStartNumber,
                'type' => $command->getType(),
                'user' => $command->getUser()
            ];
        }

        $queryData = $discsToPrintIds;
        $queryData['user'] = $command->getUser();

        $knownValues = [
            $bookmark => []
        ];
        $discNumber = (int) $command->getStartNumber();
        for ($i = 0; $i < count($discsToPrintIds); $i++) {
            $knownValues[$bookmark][$i]['discNo'] = $discNumber++;
        }

        $template = $this->params[$command->getType()]['template'];

        $documentId = $this->generateDocument($template, $queryData, $knownValues);

        $printQueue = EnqueueFileCommand::create(
            [
                'documentId' => $documentId,
                'jobName' => $command->getType() . ' Disc List',
                'user' => $command->getUser(),
                'isDiscPrinting' => true,
            ]
        );
        $printQueueResult = $this->handleSideEffect($printQueue);
        $this->result->merge($printQueueResult);
        $this->result->addMessage("Discs printed");

        if ($options) {
            $params = [
                'type' => Queue::TYPE_DISC_PRINTING,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode($options)
            ];
            $this->handleSideEffect(CreatQueue::create($params));
        }

        $this->getRepo($this->params[$command->getType()]['repo'])->setIsPrintingOn($discsToPrintIds);

        return $this->result;
    }

    protected function generateDocument($template, $queryData, $knownValues)
    {
        $dtoData = [
            'template' => $template,
            'query' => $queryData,
            'knownValues' => $knownValues,
            'description' => 'Vehicle discs',
            'category' => CategoryEntity::CATEGORY_LICENSING,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_DISCS,
            'isExternal' => false,
            'isScan' => false
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $this->result->merge($result);

        return $result->getId('document');
    }
}
