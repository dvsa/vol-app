<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\InternalConversationLink;
use Common\Service\Table\Formatter\RefDataStatus;
use Laminas\Router\Http\RouteMatch;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * InternalConversationLink test
 */
class InternalConversationLinkTest extends MockeryTestCase
{
    protected $mockRouteMatch;
    protected $mockRefDataStatus;
    protected $urlHelper;
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->mockRouteMatch = m::mock(RouteMatch::class);
        $this->mockRefDataStatus = m::mock(RefDataStatus::class);

        $this->sut = new InternalConversationLink($this->urlHelper, $this->mockRefDataStatus, $this->mockRouteMatch);

        date_default_timezone_set('Europe/London');
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     *
     * @dataProvider conversationProvider
     */
    public function testFormat($routeType, $row, $expectedRoute, $expectedParams, $expectedUrl, $expectedOutput): void
    {
        $times = 1;
        if ($expectedOutput == 'DomainException') {
            $this->expectException(\DomainException::class);
            $times = 0;
        }
        $this->mockRouteMatch
            ->shouldReceive('getParam')
            ->once()
            ->with('type')
            ->andReturn($routeType);

        foreach ($expectedParams as $param => $value) {
            $this->mockRouteMatch
                ->shouldReceive('getParam')
                ->with($param)
                ->andReturn($value);
        }

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($expectedRoute, $expectedParams)
            ->times($times)
            ->andReturn($expectedUrl);

        $this->assertEquals($expectedOutput, $this->sut->format($row));
    }

    /**
     * @dataProvider conversationTimezoneProvider
     */
    public function testInternalFormatSetsCreatedOnToDefaultTimezone(
        $routeType,
        $row,
        $expectedRoute,
        $expectedParams,
        $expectedUrl,
    ): void {
        $this->mockRouteMatch
            ->expects('getParam')
            ->with('type')
            ->andReturns($routeType);

        foreach ($expectedParams as $param => $value) {
            $this->mockRouteMatch
                ->allows('getParam')
                ->with($param)
                ->andReturns($value);
        }

        $this->urlHelper
            ->allows('fromRoute')
            ->with($expectedRoute, $expectedParams)
            ->andReturns($expectedUrl);

        $result = $this->sut->format($row);

        // Extract formatted date string
        preg_match('/<p class="govuk-body govuk-!-margin-1">(.*?)<\/p>/', $result, $matches);
        $formattedDate = $matches[1] ?? null;

        // Parse createdOn and convert to default timezone
        $createdOn = \DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            $row['createdOn']
        )->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        $expectedFormatted = $createdOn->format('l j F Y \a\t H:ia');

        $this->assertNotNull($formattedDate, 'Formatted date was not found in output.');
        $this->assertSame($expectedFormatted, $formattedDate);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function conversationProvider()
    {
        return [
            'application_route' => [
                'application',
                [
                    'id' => 123,
                    'userContextStatus' => 'NEW_MESSAGE',
                    'subject' => 'Test Conversation 1',
                    'createdOn' => '2023-08-12T12:00:00+00:00',
                    'task' => [
                        'application' => ['id' => 1000001],
                        'licence' => ['id' => 710, 'licNo' => 'OK2000001']
                    ]
                ],
                'lva-application/conversation/view',
                ['application' => 1000001, 'conversation' => 123],
                '/application/1000001/conversation/123/',
                '<a class="govuk-body govuk-link govuk-!-padding-right-1 govuk-!-font-weight-bold" href="/application/1000001/conversation/123/">OK2000001 / 1000001: Test Conversation 1</a><strong class="govuk-tag govuk-tag--red">New message</strong><br><p class="govuk-body govuk-!-margin-1">Saturday 12 August 2023 at 13:00pm</p>'
            ],
            'licence_route' => [
                'licence',
                [
                    'id' => 124,
                    'userContextStatus' => 'OPEN',
                    'subject' => 'Test Conversation 2',
                    'createdOn' => '2023-08-12T13:00:00+00:00',
                    'task' => [
                        'licence' => ['id' => 710, 'licNo' => 'OK2000002']
                    ]
                ],
                'licence/conversation/view',
                ['licence' => 710, 'conversation' => 124],
                '/licence/710/conversation/124/',
                '<a class="govuk-body govuk-link govuk-!-padding-right-1 " href="/licence/710/conversation/124/">OK2000002: Test Conversation 2</a><strong class="govuk-tag govuk-tag--blue">Open</strong><br><p class="govuk-body govuk-!-margin-1">Saturday 12 August 2023 at 14:00pm</p>'
            ],
            'case_route' => [
                'case',
                [
                    'id' => 125,
                    'userContextStatus' => 'CLOSED',
                    'subject' => 'Test Conversation 3',
                    'createdOn' => '2023-08-12T14:00:00+00:00',
                    'task' => [
                        'licence' => ['id' => 710, 'licNo' => 'OK2000003'],
                        'case' => ['id' => 101]
                    ]
                ],
                'case_conversation/view',
                ['licence' => 710, 'case' => 101, 'conversation' => 125],
                '/case/101/conversation/125/',
                '<a class="govuk-body govuk-link govuk-!-padding-right-1 " href="/case/101/conversation/125/">OK2000003: Test Conversation 3</a><strong class="govuk-tag govuk-tag--grey">Closed</strong><br><p class="govuk-body govuk-!-margin-1">Saturday 12 August 2023 at 15:00pm</p>'
            ],
            'busReg_route' => [
                'busReg',
                [
                    'id' => 127,
                    'userContextStatus' => 'OPEN',
                    'subject' => 'Test Conversation 4',
                    'createdOn' => '2023-08-12T16:00:00+00:00',
                    'task' => [
                        'licence' => ['id' => 710, 'licNo' => 'OK2000004']
                    ]
                ],
                'licence/bus_conversation/view',
                ['licence' => 710, 'busRegId' => 201, 'conversation' => 127],
                '/licence/710/bus-registration/201/conversation/127/',
                '<a class="govuk-body govuk-link govuk-!-padding-right-1 " href="/licence/710/bus-registration/201/conversation/127/">OK2000004: Test Conversation 4</a><strong class="govuk-tag govuk-tag--blue">Open</strong><br><p class="govuk-body govuk-!-margin-1">Saturday 12 August 2023 at 17:00pm</p>'
            ],
            'irhp-application_route' => [
                'irhp-application',
                [
                    'id' => 128,
                    'userContextStatus' => 'NEW_MESSAGE',
                    'subject' => 'Test Conversation 5',
                    'createdOn' => '2023-08-12T17:00:00+00:00',
                    'task' => [
                        'licence' => ['id' => 710, 'licNo' => 'OK2000005']
                    ]
                ],
                'licence/irhp-application-conversation/view',
                ['licence' => 710, 'irhpAppId' => 301, 'conversation' => 128],
                '/licence/710/irhp-application/301/conversation/128/',
                '<a class="govuk-body govuk-link govuk-!-padding-right-1 govuk-!-font-weight-bold" href="/licence/710/irhp-application/301/conversation/128/">OK2000005: Test Conversation 5</a><strong class="govuk-tag govuk-tag--red">New message</strong><br><p class="govuk-body govuk-!-margin-1">Saturday 12 August 2023 at 18:00pm</p>'
            ],
            'invalid_route' => [
                'invalid',
                [],
                null,
                [],
                '',
                'DomainException'
            ]
        ];
    }
    /**
    * @dataProvider conversationTimezoneProvider
    **/
    public function conversationTimezoneProvider(): array
    {
        return [
            'UTC date (winter)' => [
                'application',
                [
                    'id' => 101,
                    'userContextStatus' => 'NEW_MESSAGE',
                    'subject' => 'Winter Test',
                    'createdOn' => '2025-12-15T10:00:00+00:00', // UTC in winter
                    'task' => [
                        'application' => ['id' => 2001],
                        'licence' => ['id' => 1, 'licNo' => 'LIC001']
                    ]
                ],
                'lva-application/conversation/view',
                ['application' => 2001, 'conversation' => 101],
                '/application/2001/conversation/101/'
            ],
            'BST date (summer)' => [
                'application',
                [
                    'id' => 102,
                    'userContextStatus' => 'NEW_MESSAGE',
                    'subject' => 'Summer Test',
                    'createdOn' => '2023-05-15T10:00:00+00:00', // Should convert to 11:00am in BST
                    'task' => [
                        'application' => ['id' => 2002],
                        'licence' => ['id' => 1, 'licNo' => 'LIC002']
                    ]
                ],
                'lva-application/conversation/view',
                ['application' => 2002, 'conversation' => 102],
                '/application/2002/conversation/102/'
            ],
        ];
    }
}
