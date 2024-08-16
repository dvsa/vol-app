<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\BusReg;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;

/**
 * Class Text2
 * @package Dvsa\Olcs\Api\Service\Publication\Process\BusReg
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Text2 implements ProcessInterface
{
    protected $tradingAs = 'T/A %s';

    /**
     * @param PublicationLink $publication
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    public function process(PublicationLink $publication, ImmutableArrayObject $context)
    {
        $organisation = $publication->getLicence()->getOrganisation();
        $tradingNames = $organisation->getTradingNames();

        $licence = $organisation->getName();

        if (!$tradingNames->isEmpty()) {
            $latestTradingName = $tradingNames->last();
            $licence .= " " . sprintf($this->tradingAs, $latestTradingName->getName());
        }

        if ($context->offsetExists('licenceAddress')) {
            $licence .= ', ' . $context->offsetGet('licenceAddress');
        }

        $publication->setText2(strtoupper($licence));

        return $publication;
    }
}
