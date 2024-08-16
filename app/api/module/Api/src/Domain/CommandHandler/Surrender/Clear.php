<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class Clear extends AbstractSurrenderCommandHandler
{
    /**
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Surrender $surrender */
        $surrender = $this->getSurrender($command->getId());

        $surrender->setCommunityLicenceDocumentInfo(null);
        $surrender->setCommunityLicenceDocumentStatus(null);
        $surrender->setDigitalSignature(null);
        $surrender->setDiscDestroyed(null);
        $surrender->setDiscLost(null);
        $surrender->setDiscLostInfo(null);
        $surrender->setDiscStolen(null);
        $surrender->setDiscStolenInfo(null);
        $surrender->setLicenceDocumentInfo(null);
        $surrender->setLicenceDocumentStatus(null);
        $surrender->setSignatureType(null);
        $surrender->setEcmsChecked(null);
        $surrender->setSignatureChecked(null);

        $this->getRepo()->save($surrender);
        $this->result->addId('surrender', $surrender->getId());
        $this->result->addMessage('Surrender data successfully cleared');

        return $this->result;
    }
}
