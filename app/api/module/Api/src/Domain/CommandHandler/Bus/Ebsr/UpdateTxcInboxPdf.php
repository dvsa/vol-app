<?php

/**
 * Update TxcInbox
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf as UpdateTxcInboxPdfCmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;

/**
 * Update TxcInbox
 */
final class UpdateTxcInboxPdf extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TxcInbox';

    protected $extraRepos = ['Bus'];

    /**
     * Updates the txc inbox record with either timetable or dvsa route pdfs
     *
     * @param CommandInterface|UpdateTxcInboxPdfCmd $command the update command
     *
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var BusRegEntity $busReg */
        $busReg = $this->getRepo('Bus')->fetchUsingId($command);
        $txcInboxRecords = $busReg->getTxcInboxs();

        $result->addMessage($txcInboxRecords->count() . ' TxcInbox records found');

        $pdfDocument = $this->getRepo()->getReference(DocumentEntity::class, $command->getDocument());

        $count = 0;

        //the format of pdf type is enforced by the command, but we'll also make sure of it in here
        $method = 'set' . ucwords(strtolower($command->getPdfType())) . 'Document';

        /** @var TxcInbox $txcInbox */
        foreach ($txcInboxRecords as $txcInbox) {
            $txcInbox->$method($pdfDocument);
            $this->getRepo()->save($txcInbox);
            $count++;
        }

        $result->addMessage($count . ' TxcInbox records updated');

        return $result;
    }
}
