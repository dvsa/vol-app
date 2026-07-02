<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\ExternalConversationStatus;
use PHPUnit\Framework\TestCase;

/**
 * ExternalConversationStatus test
 */
class ExternalConversationStatusTest extends TestCase
{
    /**
     * Test messaging conversation status formatter
     *
     * @dataProvider statusProvider
     */
    public function testFormat($row, $expected): void
    {
        $formatter = new ExternalConversationStatus();
        $this->assertEquals($expected, $formatter->format($row));
    }

    /**
     * @return array
     */
    public function statusProvider(): array
    {
        return [
            'new_message' => [
                ['userContextStatus' => 'NEW_MESSAGE'],
                '<strong class="govuk-tag govuk-tag--red">New message</strong>',
            ],
            'open' => [
                ['userContextStatus' => 'OPEN'],
                '<strong class="govuk-tag govuk-tag--blue">Open</strong>',
            ],
            'closed' => [
                ['userContextStatus' => 'CLOSED'],
                '<strong class="govuk-tag govuk-tag--grey">Closed</strong>',
            ],
            'default' => [
                ['userContextStatus' => 'DEFAULT_OTHER_STATUS'],
                '<strong class="govuk-tag govuk-tag--green">Default other status</strong>',
            ],
        ];
    }
}
