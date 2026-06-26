<?php

namespace CommonTest\Common\Service\Data;

use Common\Service\Data\UserTypesListDataService;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class UserTypesListDataService
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UserTypesListDataServiceTest extends MockeryTestCase
{
    public function testFetchListOptions(): void
    {
        $sut = new UserTypesListDataService();

        $options = $sut->fetchListOptions();
        $this->assertArrayHasKey('internal', $options);
    }
}
