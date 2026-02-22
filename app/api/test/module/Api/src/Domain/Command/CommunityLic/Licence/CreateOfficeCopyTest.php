<?php

declare(strict_types=1);

/**
 * Create Office Copy Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\CommunityLic\Licence;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\Licence\CreateOfficeCopy;

/**
 * Create Office Copy Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateOfficeCopyTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CreateOfficeCopy::create(
            [
                'licence' => 1,
            ]
        );

        $this->assertEquals(1, $command->getLicence());
    }
}
