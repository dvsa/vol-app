<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Common\Service\Table\Formatter\NameActionAndStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class NameActionAndStatusTest extends MockeryTestCase
{
    private const int TEST_ID = 12345;

    private const string TEST_TITLE = 'TEST_TITLE';

    private const string TEST_FORENAME = '';

    private const string TEST_FAMILY_NAME = '';

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $isInternalReadOnly, $expected): void
    {
        $mockPermissionService = m::mock(Permission::class);
        $mockPermissionService->expects('isInternalReadOnly')->withNoArgs()->andReturn($isInternalReadOnly);
        $this->assertEquals($expected, new NameActionAndStatus($mockPermissionService)->format($data));
    }

    public static function provider(): \Iterator
    {
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
    }
}
