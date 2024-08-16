<?php

namespace Dvsa\OlcsTest\Api\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\RolePermission;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\User\Role as Entity;

/**
 * Role Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class RoleEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testEntityName()
    {
        $roleEntity = new Entity();
        $roleEntity->setRole('admin');

        $this->assertEquals('admin', $roleEntity->getName());
    }

    public function testHasPermission()
    {
        $roleEntity = new Entity();
        $roleEntity->setRole('admin');

        $roleEntity->setRolePermissions(
            new ArrayCollection(
                [
                    (new RolePermission())
                        ->setPermission(
                            (new Permission())
                                ->setName('permissionOne')
                        ),
                    (new RolePermission())
                        ->setPermission(
                            (new Permission())
                                ->setName('permissionTwo')
                        ),
                ]
            )
        );

        $this->assertTrue($roleEntity->hasPermission('permissionOne'));
    }

    public function testDoesNotHavePermission()
    {
        $roleEntity = new Entity();
        $roleEntity->setRole('admin');

        $roleEntity->setRolePermissions(
            new ArrayCollection(
                [
                    (new RolePermission())
                        ->setPermission(
                            (new Permission())
                                ->setName('permissionOne')
                        ),
                    (new RolePermission())
                        ->setPermission(
                            (new Permission())
                                ->setName('permissionTwo')
                        ),
                ]
            )
        );

        $this->assertFalse($roleEntity->hasPermission('permissionThree'));
    }

    public function testAnon()
    {
        $role = new Entity();
        $anon = $role->anon();
        $this->assertEquals($anon->getId(), Entity::ROLE_ANON);
        $this->assertEquals($anon->getRole(), Entity::ROLE_ANON);
    }

    /**
     * @dataProvider dpGetAllowedRoles
     */
    public function testGetAllowedRoles($role, $emptyExpected)
    {
        $roleEntity = new Entity();
        $roleEntity->setRole($role);

        $result = $roleEntity->getAllowedRoles();

        $this->assertIsArray($result);
        $emptyExpected ? $this->assertEmpty($result) : $this->assertNotEmpty($result);
    }

    public function dpGetAllowedRoles()
    {
        return [
            [Entity::ROLE_SYSTEM_ADMIN, false],
            [Entity::ROLE_INTERNAL_ADMIN, false],
            [Entity::ROLE_INTERNAL_IRHP_ADMIN, false],
            [Entity::ROLE_INTERNAL_CASE_WORKER, false],
            [Entity::ROLE_INTERNAL_READ_ONLY, true],
            [Entity::ROLE_INTERNAL_LIMITED_READ_ONLY, true],
        ];
    }
}
