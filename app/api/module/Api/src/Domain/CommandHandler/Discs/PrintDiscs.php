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
use Dvsa\Olcs\Api\Entity\System\SystemParameter;

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
    protected $extraRepos = ['PsvDisc', 'SystemParameter'];

    /**
     * Suffix appended to the doc-store template name when the pinned layout
     * toggle is on: the *Gotenberg templates are new files carrying explicit
     * page geometry for the LibreOffice renderer, so the legacy templates
     * (and renderer behaviour) stay untouched while the toggle is off.
     */
    private const GOTENBERG_TEMPLATE_SUFFIX = 'Gotenberg';

    private $params = [
        'PSV' => [
            'template' => 'PSVDiscTemplate',
            'bookmark' => 'Psv_Disc_Page',
            'repo' => 'PsvDisc',
            'pinnedToggle' => SystemParameter::PSV_DISC_PINNED_LAYOUT
        ],
        'Goods' => [
            'template' => 'GVDiscTemplate',
            'bookmark' => 'Disc_List',
            'repo' => 'GoodsDisc',
            'pinnedToggle' => SystemParameter::GOODS_DISC_PINNED_LAYOUT
        ]
    ];

    /**
     * @param CommandInterface|\Dvsa\Olcs\Api\Domain\Command\Discs\PrintDiscs $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    #[\Override]
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
        if ($this->isPinnedLayout($command->getType())) {
            $template .= self::GOTENBERG_TEMPLATE_SUFFIX;
        }

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

    /**
     * Same toggle read as the DiscList/PsvDiscPage bookmarks, so the base
     * template and the snippet variant always switch together.
     */
    private function isPinnedLayout(string $type): bool
    {
        return $this->getRepo('SystemParameter')
            ->fetchIsEnabled($this->params[$type]['pinnedToggle']);
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
