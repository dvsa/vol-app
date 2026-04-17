<?php

declare(strict_types=1);

/**
 * Resolve Payment Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Transaction;

use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;

/**
 * Resolve Payment Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ResolvePaymentTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $data = [
            'id' => 99,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE
        ];

        $command = ResolvePayment::create($data);

        $this->assertEquals(99, $command->getId());
        $this->assertEquals(FeeEntity::METHOD_CARD_ONLINE, $command->getPaymentMethod());

        $this->assertEquals(
            [
                'id' => 99,
                'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
            ],
            $command->getArrayCopy()
        );
    }
}
