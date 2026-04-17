<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\PrintScan;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as Entity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;

/**
 * Printer Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PrinterEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCanDelete(): void
    {
        $printer = new Entity();
        $this->assertTrue($printer->canDelete());

        $printer->addTeamPrinters(new \Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter(new TeamEntity(), $printer));
        $this->assertFalse($printer->canDelete());
    }
}
