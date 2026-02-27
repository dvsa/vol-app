<?php

declare(strict_types=1);

/**
 * Dispatch Document Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\DispatchDocument;

/**
 * Create Document Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DispatchDocumentTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = DispatchDocument::create(
            [
                'user' => 1
            ]
        );

        $this->assertEquals(1, $command->getUser());
    }
}
