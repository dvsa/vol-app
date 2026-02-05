<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackFailed;

/**
 * Update Txc Inbox PDF Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ProcessPackFailedTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $id = 1;
        $organisation = 2;

        $command = ProcessPackFailed::create(
            [
                'id' => $id,
                'organisation' => $organisation,
            ]
        );

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($organisation, $command->getOrganisation());
    }
}
