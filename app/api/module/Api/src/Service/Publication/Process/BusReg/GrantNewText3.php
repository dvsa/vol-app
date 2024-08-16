<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\BusReg;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;

/**
 * Class GrantNewText3
 * @package Dvsa\Olcs\Api\Service\Publication\Process\BusReg
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class GrantNewText3 implements ProcessInterface
{
    protected $from = 'From: %s';
    protected $to = 'To: %s';
    protected $via = 'Via: %s';
    protected $serviceDesignation = 'Name or No.: %s';
    protected $serviceType = 'Service type: %s';
    protected $effectiveDate = 'Effective date: %s';
    protected $endDate = 'End date: %s';
    protected $otherDetails = 'Other details: %s';
    protected $dateFormat = 'd F Y';

    /**
     * @param PublicationLink $publication
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    public function process(PublicationLink $publication, ImmutableArrayObject $context)
    {
        $busReg = $publication->getBusReg();

        $parts = [];

        $parts[] = sprintf($this->from, $busReg->getStartPoint());
        $parts[] = sprintf($this->to, $busReg->getFinishPoint());
        $parts[] = sprintf($this->via, $busReg->getVia());
        $parts[] = sprintf($this->serviceDesignation, $context->offsetGet('busServices'));
        $parts[] = sprintf($this->serviceType, $context->offsetGet('busServiceTypes'));

        $effectiveDate = new \DateTime($busReg->getEffectiveDate());
        $parts[] = sprintf($this->effectiveDate, $effectiveDate->format($this->dateFormat));

        $endDate = $busReg->getEndDate();

        if (!is_null($endDate)) {
            $endDateTime = new \DateTime($endDate);
            $parts[] = sprintf($this->endDate, $endDateTime->format($this->dateFormat));
        }

        $parts[] = sprintf($this->otherDetails, $busReg->getOtherDetails());

        $publication->setText3(implode("\n", $parts));

        return $publication;
    }
}
