<?php

declare(strict_types=1);

/**
 * CreateNextPublicationTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Publication;

use Dvsa\Olcs\Api\Domain\Command\Publication\CreateNextPublication;

/**
 * CreateNextPublicationTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CreateNextPublicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $id = 5;
        $command = CreateNextPublication::create(['id' => $id]);
        $this->assertEquals($id, $command->getId());
    }
}
