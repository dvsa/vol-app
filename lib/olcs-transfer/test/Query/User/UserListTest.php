<?php

namespace Dvsa\OlcsTest\Transfer\Query\User;

use Dvsa\Olcs\Transfer\Query\User\UserList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\User\UserList
 */
class UserListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'sort' => 'p.forename',
            'order' => 'ASC',
            'organisation' => 1,
            'team' => 12,
            'isInternal' => true,
            'roles' => ['operator-user', 'operator-tm']
        ];

        $sut = UserList::create($data);

        static::assertEquals('p.forename', $sut->getSort());
        static::assertEquals('ASC', $sut->getOrder());
        static::assertEquals(1, $sut->getOrganisation());
        static::assertEquals(12, $sut->getTeam());
        static::assertEquals(true, $sut->getIsInternal());
        static::assertEquals(['operator-user', 'operator-tm'], $sut->getRoles());
    }
}
