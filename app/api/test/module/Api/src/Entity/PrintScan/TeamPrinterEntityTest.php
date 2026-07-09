<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\PrintScan;

use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter as Entity;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;

/**
 * TeamPrinter Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class TeamPrinterEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreate(): void
    {
        $team = new TeamEntity();
        $team->setId(1);
        $printer = new PrinterEntity();
        $printer->setId(2);
        $teamPrinter = new TeamPrinter($team, $printer);
        $this->assertEquals(1, $teamPrinter->getTeam()->getId());
        $this->assertEquals(2, $teamPrinter->getPrinter()->getId());
    }
}
