<?php

/**
 * UserWitnName data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\UserWithName;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\User\UserList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * UserWithName data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UserWithNameTest extends AbstractDataServiceTestCase
{
    /**
     * Test fetchListOptions
     */
    public function testFetchListOptions()
    {
        $users = [
            [
                'id' => 1,
                'contactDetails' => [
                    'person' => [
                        'forename' => 'foo',
                        'familyName' => 'bar'
                    ]
                ]
            ],
            [
                'id' => 2,
                'loginId' => 'cake'
            ]
        ];
        $expected = [
            1 => 'foo bar',
            2 => 'cake'
        ];
        $sut = new UserWithName();
        $sut->setData('userlist', $users);

        $this->assertEquals($expected, $sut->fetchListOptions([]));
    }

    /**
     * Test fetchListOptions with empty data
     */
    public function testFetchListOptionsEmpty()
    {
        $sut = new UserWithName();
        $sut->setData('userlist', 'foo');

        $this->assertEquals([], $sut->fetchListOptions([]));
    }
}
