<?php

declare(strict_types=1);

/**
 * CreateFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee;

/**
 * CreateFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CreateFee::create(
            [
                'id' => 111,
                'foo' => 'bar',
                'feeTypeFeeType' => 'feetype',
                'optional' => true
            ]
        );

        $this->assertEquals(111, $command->getId());
        $this->assertEquals('feetype', $command->getFeeTypeFeeType());
        $this->assertTrue($command->getOptional());
    }
}
