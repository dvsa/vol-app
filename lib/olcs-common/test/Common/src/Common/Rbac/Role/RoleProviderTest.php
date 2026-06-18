<?php

namespace CommonTest\Common\Rbac\Role;

use Common\Rbac\Role\RoleProvider;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Rbac\Role\Role;

/**
 * Class RoleProviderTest
 * @package CommonTest\Rbac\Role
 */
class RoleProviderTest extends TestCase
{
    public function testGetUserData(): void
    {
        $resultsData = [
            [
                'role' => 'role1',
                'rolePermissions' => [
                    [
                        'permission' => ['name' => 'perm1']
                    ],
                    [
                        'permission' => ['name' => 'perm2']
                    ],
                ]
            ],
            [
                'role' => 'role2',
                'rolePermissions' => [
                    [
                        'permission' => ['name' => 'perm2']
                    ],
                    [
                        'permission' => ['name' => 'perm3']
                    ],
                ]
            ],
            [
                'role' => 'role3'
            ]
        ];

        $mockResponse = m::mock();
        $mockResponse->shouldReceive('isOk')->andReturn(true);
        $mockResponse->shouldReceive('getResult')->andReturn(
            [
                'results' => $resultsData
            ]
        );

        $mockQueryService = m::mock(QuerySender::class);
        $mockQueryService->shouldReceive('send')->andReturn($mockResponse);

        $sut = new RoleProvider($mockQueryService);
        $result = $sut->getRoles(['role1', 'role3']);

        $this->assertEquals(2, count($result));

        $this->assertInstanceOf(Role::class, $result['role1']);
        $this->assertTrue($result['role1']->hasPermission('perm1'));
        $this->assertTrue($result['role1']->hasPermission('perm2'));
        $this->assertFalse($result['role1']->hasPermission('perm3'));

        $this->assertFalse(isset($result['role2']));

        $this->assertInstanceOf(Role::class, $result['role3']);
        $this->assertFalse($result['role3']->hasPermission('perm1'));
        $this->assertFalse($result['role3']->hasPermission('perm2'));
        $this->assertFalse($result['role3']->hasPermission('perm3'));
    }

    public function testGetUserDataThrowsUnableToRetrieveException(): void
    {
        $this->expectException('RuntimeException');

        $mockResponse = m::mock();
        $mockResponse->shouldReceive('isOk')->andReturn(false);

        $mockQueryService = m::mock(QuerySender::class);
        $mockQueryService->shouldReceive('send')->andReturn($mockResponse);

        $sut = new RoleProvider($mockQueryService);
        $sut->getRoles([]);
    }
}
