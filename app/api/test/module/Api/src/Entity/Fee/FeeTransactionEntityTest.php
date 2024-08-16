<?php

namespace Dvsa\OlcsTest\Api\Entity\Fee;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * FeeTransaction Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class FeeTransactionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = $this->instantiate($this->entityClass);
    }

    /**
     * @param boolean $expected
     * @dataProvider isRefundedProvider
     */
    public function testIsRefundedOrReversed(array $feeTransactions, $expected)
    {
        $this->sut->setReversingFeeTransactions(new ArrayCollection($feeTransactions));

        $this->assertSame($expected, $this->sut->isRefundedOrReversed());
    }

    public function isRefundedProvider()
    {
        return [
            [
                [],
                false,
            ],
            [
                [
                    m::mock(Entity::class),
                ],
                true,
            ]
        ];
    }
}
