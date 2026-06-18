<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Common\Service\Table\Formatter\NameActionAndStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class NameActionAndStatusTest extends MockeryTestCase
{
    private const TEST_ID = 12345;

    private const TEST_TITLE = 'TEST_TITLE';

    private const TEST_FORENAME = '';

    private const TEST_FAMILY_NAME = '';

    /**
     * @dataProvider provider
     */
    public function testFormat($data, $isInternalReadOnly, $expected): void
    {
        $mockPermissionService = m::mock(Permission::class);
        $mockPermissionService->expects('isInternalReadOnly')->withNoArgs()->andReturn($isInternalReadOnly);
        $this->assertEquals($expected, (new NameActionAndStatus($mockPermissionService))->format($data));
    }

    public function provider(): array
    {
        return [
            [
                [
                    'id' => self::TEST_ID,
                    'forename' => self::TEST_FORENAME,
                    'familyName' => self::TEST_FAMILY_NAME,
                    'title' => [
                        'description' => ''
                    ],
                    'status' => null
                ],
                false,
                sprintf(
                    NameActionAndStatus::BUTTON_FORMAT,
                    self::TEST_ID,
                    self::TEST_FORENAME . ' ' . self::TEST_FAMILY_NAME
                ),
            ],
            [
                [
                    'id' => self::TEST_ID,
                    'forename' => self::TEST_FORENAME,
                    'familyName' => self::TEST_FAMILY_NAME,
                    'title' => [
                        'description' => self::TEST_TITLE
                    ],
                    'status' => null
                ],
                false,
                sprintf(
                    NameActionAndStatus::BUTTON_FORMAT,
                    self::TEST_ID,
                    self::TEST_TITLE . ' ' . self::TEST_FORENAME . ' ' . self::TEST_FAMILY_NAME
                ),
            ],
            [
                [
                    'id' => self::TEST_ID,
                    'forename' => self::TEST_FORENAME,
                    'familyName' => self::TEST_FAMILY_NAME,
                    'title' => [
                        'description' => self::TEST_TITLE
                    ],
                    'status' => 'new'
                ],
                false,
                sprintf(
                    NameActionAndStatus::BUTTON_FORMAT,
                    self::TEST_ID,
                    self::TEST_TITLE . ' ' . self::TEST_FORENAME . ' ' . self::TEST_FAMILY_NAME
                ) . ' <span class="overview__status green">New</span>'
            ],
            [
                [
                    'id' => self::TEST_ID,
                    'forename' => self::TEST_FORENAME,
                    'familyName' => self::TEST_FAMILY_NAME,
                    'title' => [
                        'description' => self::TEST_TITLE
                    ],
                    'status' => null
                ],
                true,
                self::TEST_TITLE . ' ' . self::TEST_FORENAME . ' ' . self::TEST_FAMILY_NAME,
            ],
            [
                [
                    'id' => self::TEST_ID,
                    'forename' => self::TEST_FORENAME,
                    'familyName' => self::TEST_FAMILY_NAME,
                    'title' => [
                        'description' => self::TEST_TITLE
                    ],
                    'status' => 'new'
                ],
                true,
                self::TEST_TITLE . ' ' . self::TEST_FORENAME . ' ' . self::TEST_FAMILY_NAME
                . ' <span class="overview__status green">New</span>'
            ]
        ];
    }
}
