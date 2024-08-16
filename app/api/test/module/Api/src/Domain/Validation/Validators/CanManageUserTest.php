<?php

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

    /**
     * @dataProvider provider
     */
    public function testIsValid($canManageUser, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $entity = m::mock(User::class);

        $repo = $this->mockRepo('User');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsGranted(Permission::CAN_MANAGE_USER_SELFSERVE, $canManageUser, $entity);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidWithoutId($canManageUser, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->setIsGranted(Permission::CAN_MANAGE_USER_SELFSERVE, $canManageUser, null);

        $this->assertEquals($expected, $this->sut->isValid(null));
    }

    public function testIsValidInternal()
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertEquals(true, $this->sut->isValid(111));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
