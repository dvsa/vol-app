<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\User;

use Dvsa\Olcs\Transfer\Query\User\UserListInternal;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\User\UserListInternal::class)]
final class UserListInternalTest extends MockeryTestCase
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

        $this->assertEquals('p.forename', $sut->getSort());
        $this->assertEquals('ASC', $sut->getOrder());
        $this->assertEquals(12, $sut->getTeam());
        $this->assertEquals(true, $sut->getExcludeLimitedReadOnly());
    }
}
