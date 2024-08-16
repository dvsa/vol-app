<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Repository\Sla;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculator;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

trait SlaCalculatorAwareTrait
{
    /**
     * @var SlaCalculatorInterface|SlaCalculator
     */
    protected $slaCalculator;

    public function setSlaCalculator(SlaCalculatorInterface $service)
    {
        $this->slaCalculator = $service;
    }

    /**
     * @return SlaCalculatorInterface|SlaCalculator
     */
    public function getSlaCalculator()
    {
        return $this->slaCalculator;
    }

    protected function setTargetCompletionDateForApplication(Application $application)
    {
        // Get SLA
        $slaRepo = $this->getRepo('Sla');
        assert($slaRepo instanceof Sla);

        $sla = $slaRepo->fetchByCategoryFieldAndCompareTo('application', 'receivedDate', 'targetCompletionDate');

        $application->setTargetCompletionDate(
            $this->slaCalculator->applySla(
                $application->getReceivedDate(true),
                $sla,
                null
            )
        );
    }
}
