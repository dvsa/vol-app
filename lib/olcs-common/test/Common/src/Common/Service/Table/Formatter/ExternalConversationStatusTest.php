<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\ExternalConversationStatus;
use PHPUnit\Framework\TestCase;

/**
 * ExternalConversationStatus test
 */
final class ExternalConversationStatusTest extends TestCase
{
    /**
     * Test messaging conversation status formatter
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testFormat($row, $expected): void
    {
        $formatter = new ExternalConversationStatus();
        $this->assertEquals($expected, $formatter->format($row));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function statusProvider(): \Iterator
    {
        yield 'new_message' => [
            ['userContextStatus' => 'NEW_MESSAGE'],
            '<strong class="govuk-tag govuk-tag--red">New message</strong>',
        ];
        yield 'open' => [
            ['userContextStatus' => 'OPEN'],
            '<strong class="govuk-tag govuk-tag--blue">Open</strong>',
        ];
        yield 'closed' => [
            ['userContextStatus' => 'CLOSED'],
            '<strong class="govuk-tag govuk-tag--grey">Closed</strong>',
        ];
        yield 'default' => [
            ['userContextStatus' => 'DEFAULT_OTHER_STATUS'],
            '<strong class="govuk-tag govuk-tag--green">Default other status</strong>',
        ];
    }
}
