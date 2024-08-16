<?php

/**
 * Identity Test
 */

namespace Dvsa\OlcsTest\Api\Rbac;

use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Rbac\Identity;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Identity Test
 */
class IdentityTest extends MockeryTestCase
{
    public function testGetUser()
    {
        $user = User::anon();

        $sut = new Identity($user);

        $this->assertSame($user, $sut->getUser());
    }

    public function testGetRoles()
    {
        $user = User::anon();

        $sut = new Identity($user);

        $this->assertEquals([RoleEntity::ROLE_ANON], $sut->getRoles());
    }
}
