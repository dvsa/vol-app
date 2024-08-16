<?php

/**
 * Create TxcInbox
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateTxcInbox as CreateTxcInboxCmd;
use Doctrine\ORM\Query;

/**
 * Create TxcInbox
 */
final class CreateTxcInbox extends AbstractCommandHandler
{
    protected $repoServiceName = 'Bus';

    /**
     * Create TXC Inbox records for a bus reg
     *
     * @param CommandInterface|CreateTxcInboxCmd $command command to create txc inbox
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var BusRegEntity $busReg
         * @var EbsrSubmissionEntity $ebsrSubmission
         * @var LocalAuthorityEntity $localAuthority
         */
        $busReg = $this->getRepo('Bus')->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $ebsrSubmission = $busReg->getEbsrSubmissions()->first();
        $localAuthorities = $busReg->getLocalAuthoritys();
        $organisation = $ebsrSubmission->getOrganisation();
        $zipDocument = $ebsrSubmission->getDocument();

        $result = new Result();

        $inboxRecords = new ArrayCollection();

        foreach ($localAuthorities as $localAuthority) {
            $inboxRecords->add(new TxcInbox($busReg, $zipDocument, $localAuthority));
            $result->addMessage('Txc Inbox record created for ' . $localAuthority->getDescription());
        }

        $inboxRecords->add(new TxcInbox($busReg, $zipDocument, null, $organisation));
        $busReg->setTxcInboxs($inboxRecords);
        $this->getRepo('Bus')->save($busReg);
        $result->addMessage('Txc Inbox record created for ' . $organisation->getName());

        return $result;
    }
}
