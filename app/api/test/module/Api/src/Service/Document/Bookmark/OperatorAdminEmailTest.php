<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\OperatorAdminEmail;
use PHPUnit\Framework\TestCase;

/**
 * OperatorAdminEmail test
 */
class OperatorAdminEmailTest extends TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new OperatorAdminEmail();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithMultipleAdmins(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => [
                    [
                        'isAdministrator' => 'Y',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'admin1@test.com'
                            ]
                        ]
                    ],
                    [
                        'isAdministrator' => 'Y',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'admin2@test.com'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('admin1@test.com, admin2@test.com', $bookmark->render());
    }

    public function testRenderWithSingleAdmin(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => [
                    [
                        'isAdministrator' => 'Y',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'admin@test.com'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('admin@test.com', $bookmark->render());
    }

    public function testRenderWithNoAdmins(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => [
                    [
                        'isAdministrator' => 'N',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'user@test.com'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('', $bookmark->render());
    }

    public function testRenderWithNoOrganisationUsers(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => []
            ]
        ]);

        $this->assertEquals('', $bookmark->render());
    }

    public function testRenderSkipsUsersWithoutEmail(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => [
                    [
                        'isAdministrator' => 'Y',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => null
                            ]
                        ]
                    ],
                    [
                        'isAdministrator' => 'Y',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'admin@test.com'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('admin@test.com', $bookmark->render());
    }

    public function testRenderSkipsNonAdminUsers(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => [
                    [
                        'isAdministrator' => 'N',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'user@test.com'
                            ]
                        ]
                    ],
                    [
                        'isAdministrator' => 'Y',
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'admin@test.com'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('admin@test.com', $bookmark->render());
    }

    public function testRenderWithMissingIsAdministratorFlag(): void
    {
        $bookmark = new OperatorAdminEmail();
        $bookmark->setData([
            'organisation' => [
                'organisationUsers' => [
                    [
                        // Missing isAdministrator key - should default to 'N'
                        'user' => [
                            'contactDetails' => [
                                'emailAddress' => 'user@test.com'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('', $bookmark->render());
    }
}
