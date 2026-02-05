<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command\CommunityLic;

use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Activate;

/**
 * Activate command test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ActivateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $params = [
            'communityLicenceIds' => [1, 2]
        ];
        $command = Activate::create($params);
        $this->assertEquals($command->getCommunityLicenceIds(), [1, 2]);
    }
}
