<?php

declare(strict_types=1);

/**
 * Pay Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee;

/**
 * Pay Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PayFeeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = PayFee::create(
            [
                'id' => 69,
                'foo' => 'bar',
            ]
        );

        $this->assertEquals(69, $command->getId());
    }
}
