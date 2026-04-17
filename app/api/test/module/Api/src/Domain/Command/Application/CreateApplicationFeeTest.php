<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee;

/**
 * CreateApplicationFee test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateApplicationFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CreateApplicationFee::create(
            [
                'feeTypeFeeType' => 'foo',
                'description' => 'bar',
                'optional' => true,
            ]
        );

        $this->assertEquals('foo', $command->getFeeTypeFeeType());
        $this->assertEquals('bar', $command->getDescription());
        $this->assertTrue($command->getOptional());
    }
}
