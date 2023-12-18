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
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;

/**
 * UserWithName data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UserWithNameTest extends AbstractDataServiceTestCase
{
    /** @var UserWithName */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new UserWithName($this->abstractDataServiceServices);
    }

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

        $this->sut->setData('userlist', $users);

        $this->assertEquals($expected, $this->sut->fetchListOptions([]));
    }

    /**
     * Test fetchListOptions with empty data
     */
    public function testFetchListOptionsEmpty()
    {
        $this->sut->setData('userlist', 'foo');

        $this->assertEquals([], $this->sut->fetchListOptions([]));
    }
}
