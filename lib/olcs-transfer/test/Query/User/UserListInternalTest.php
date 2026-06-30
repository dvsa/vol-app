<?php

namespace Dvsa\OlcsTest\Transfer\Query\User;

use Dvsa\Olcs\Transfer\Query\User\UserListInternal;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\User\UserListInternal
 */
class UserListInternalTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'sort' => 'p.forename',
            'order' => 'ASC',
            'team' => 12,
            'excludeLimitedReadOnly' => true,
        ];

        $sut = UserListInternal::create($data);

        static::assertEquals('p.forename', $sut->getSort());
        static::assertEquals('ASC', $sut->getOrder());
        static::assertEquals(12, $sut->getTeam());
        static::assertEquals(true, $sut->getExcludeLimitedReadOnly());
    }
}
