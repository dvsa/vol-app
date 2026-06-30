<?php

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\ExternalConversationLink;
use DateTimeInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ExternalConversationLinkLink test
 */
class ExternalConversationLinkTest extends MockeryTestCase
{
    /** @var ExternalConversationLink */
    private $sut;

    /** @var m\MockInterface */
    private $urlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new ExternalConversationLink($this->urlHelper);
        date_default_timezone_set('Europe/London');
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testExternalFormatSetsCreatedOnToDefaultTimezone(): void
    {
        $row = [
            'id' => 1,
            'userContextStatus' => 'NEW_MESSAGE',
            'createdOn' => '2025-05-09T09:36:02+0000',
            'subject' => 'Test Subject',
            'task' => [
                'licence' => ['licNo' => 'AB123'],
            ],
        ];

        // Mock the URL helper
        $this->urlHelper->expects('fromRoute')
            ->with('conversations/view', ['conversationId' => 1])
            ->andReturns('conversations/view');

        $result = $this->sut->format($row);

        // Extract date string from HTML output (it's after the <p> tag)
        preg_match('/<p class="govuk-body govuk-!-margin-1">(.*?)<\/p>/', $result, $matches);
        $formattedDateString = $matches[1];

        $createdOn = '2025-05-09T09:36:02+0000';
        $dateTime = \DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $createdOn)
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        $expectedFormattedDate = $dateTime->format('l j F Y \a\t H:ia');

        // Then assert the formatted datetime is included
        $this->assertStringContainsString($expectedFormattedDate, $formattedDateString);
    }
}
