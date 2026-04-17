<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTransaction;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTransaction
 */
class CanAccessTransactionTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessTransaction
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTransaction();

        parent::setUp();
    }

    public function testIsValidId(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Transaction::class);

        $repo = $this->mockRepo('Transaction');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], true);

        $this->assertEquals(true, $this->sut->isValid(111));
    }

    public function testIsValidReference(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Transaction::class);

        $repo = $this->mockRepo('Transaction');
        $repo->shouldReceive('fetchByReference')->with('ABC1')->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], true);

        $this->assertEquals(true, $this->sut->isValid('ABC1'));
    }
}
