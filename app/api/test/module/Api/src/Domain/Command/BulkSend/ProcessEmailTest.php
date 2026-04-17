<?php

declare(strict_types=1);

/**
 * Process Email send cmd test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\BulkSend\ProcessEmail;

class ProcessEmailTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = ProcessEmail::create(
            [
                'id' => 7,
                'templateName' => 'gv-ni-standard',
            ]
        );

        $this->assertEquals(7, $command->getId());
        $this->assertEquals('gv-ni-standard', $command->getTemplateName());
    }
}
