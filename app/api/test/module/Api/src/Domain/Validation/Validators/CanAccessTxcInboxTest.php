<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository\Bus;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTxcInbox;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

class CanAccessTxcInboxTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessTxcInbox
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTxcInbox();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidForOperatorAdmin(bool $canAccess): void
    {
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_ADMIN, null)->andReturnTrue();

        $entity = m::mock(Bus::class)->makePartial();
        $this->setIsValid('isOwner', [$entity], $canAccess);

        $repo = $this->mockRepo('Bus');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->assertEquals($canAccess, $this->sut->isValid(111));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidForOperatorTc(bool $canAccess): void
    {
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_ADMIN, null)->andReturnFalse();
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_TC, null)->andReturnTrue();

        $entity = m::mock(Bus::class)->makePartial();
        $this->setIsValid('isOwner', [$entity], $canAccess);

        $repo = $this->mockRepo('Bus');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->assertEquals($canAccess, $this->sut->isValid(111));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidForOperatorUser(bool $canAccess): void
    {
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_ADMIN, null)->andReturnFalse();
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_TC, null)->andReturnFalse();
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_USER, null)->andReturnTrue();

        $entity = m::mock(Bus::class)->makePartial();
        $this->setIsValid('isOwner', [$entity], $canAccess);

        $repo = $this->mockRepo('Bus');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->assertEquals($canAccess, $this->sut->isValid(111));
    }

    public function testIsValidWithEmptyId()
    {
        $this->assertFalse($this->sut->isValid(null));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidForLocalAuthority($canAccess): void
    {
        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_USER, null)
            ->andReturnFalse();
        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_ADMIN, null)
            ->andReturnFalse();
        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_TC, null)
            ->andReturnFalse();
        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)
            ->andReturn($canAccess);
        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)
            ->andReturn($canAccess);

        $this->assertEquals($canAccess, $this->sut->isValid(111));
    }

    public function testIsValidWithOtherUsers(): void
    {
        $this->auth->expects('isGranted')->with(m::type('string'), null)
            ->times(5)
            ->andReturnFalse();

        $this->assertFalse($this->sut->isValid(5));
    }

    public function provider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
