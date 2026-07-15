<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\User;

use Dvsa\Olcs\Transfer\Query\User\UserList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\User\UserList::class)]
final class UserListTest extends MockeryTestCase
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

        $this->assertEquals('p.forename', $sut->getSort());
        $this->assertEquals('ASC', $sut->getOrder());
        $this->assertEquals(1, $sut->getOrganisation());
        $this->assertEquals(12, $sut->getTeam());
        $this->assertEquals(true, $sut->getIsInternal());
        $this->assertEquals(['operator-user', 'operator-tm'], $sut->getRoles());
    }
}
