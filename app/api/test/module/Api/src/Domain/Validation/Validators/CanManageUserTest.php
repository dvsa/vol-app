<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanManageUser as Sut;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;

/**
 * Can Manage User Test
 */
class CanManageUserTest extends AbstractValidatorsTestCase
{
    /**
     * @var Sut
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canManageUser, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $entity = m::mock(User::class);

        $repo = $this->mockRepo('User');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsGranted(Permission::CAN_MANAGE_USER_SELFSERVE, $canManageUser, $entity);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidWithoutId(mixed $canManageUser, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->setIsGranted(Permission::CAN_MANAGE_USER_SELFSERVE, $canManageUser, null);

        $this->assertEquals($expected, $this->sut->isValid(null));
    }

    public function testIsValidInternal(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertEquals(true, $this->sut->isValid(111));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
