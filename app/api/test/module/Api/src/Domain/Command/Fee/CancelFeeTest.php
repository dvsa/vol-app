<?php

declare(strict_types=1);

/**
 * CancelFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;

/**
 * CancelFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CancelFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CancelFee::create(
            [
                'id' => 82,
                'foo' => 'bar',
            ]
        );

        $this->assertEquals(82, $command->getId());
    }
}
