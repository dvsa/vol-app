<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanReadUser as Sut;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;

/**
 * Can Read User Test
 */
class CanReadUserTest extends AbstractValidatorsTestCase
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
    public function testIsValid($canReadUser, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->setIsValid('canManageUser', [111], false);

        $entity = m::mock(User::class);

        $repo = $this->mockRepo('User');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsGranted(Permission::CAN_READ_USER_SELFSERVE, $canReadUser, $entity);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public function testIsValidCanManageUser()
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->setIsValid('canManageUser', [111], true);

        $this->assertEquals(true, $this->sut->isValid(111));
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
