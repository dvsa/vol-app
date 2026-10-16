<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Table\Formatter\ExternalConversationMessage;
use Common\Service\Table\Formatter\InternalConversationMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the stored XSS confirmed in the conversation views of both apps.
 *
 * Unlike AbstractConversationMessageTest, this exercises the real concrete formatters with no
 * protected methods mocked out — the sender name, attachment and read-receipt paths are exactly
 * where the payloads were injecting, so mocking them would defeat the purpose.
 */
final class ConversationMessageEscapingTest extends TestCase
{
    private const PAYLOAD = '<script>alert(document.domain)</script>';

    private const ATTR_PAYLOAD = 'x" onmouseover="alert(1)';

    #[\Override]
    protected function setUp(): void
    {
        date_default_timezone_set('Europe/London');
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Restored to the timezone phpunit.xml.dist declares. Leaving a foreign one behind
        // makes any later test that computes a relative date order-dependent — the provider and
        // the assertion end up on opposite sides of midnight.
        date_default_timezone_set(ini_get('date.timezone') ?: 'UTC');
    }

    public static function formatterProvider(): \Iterator
    {
        yield 'internal' => [new InternalConversationMessage()];
        yield 'external' => [new ExternalConversationMessage()];
    }

    #[DataProvider('formatterProvider')]
    public function testMessageBodyIsEscaped(object $sut): void
    {
        $output = $sut->format($this->row(['messagingContent' => ['text' => self::PAYLOAD]]));

        $this->assertStringNotContainsString(self::PAYLOAD, $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    #[DataProvider('formatterProvider')]
    public function testMessageBodyStillConvertsNewlinesToBreaks(object $sut): void
    {
        $output = $sut->format($this->row(['messagingContent' => ['text' => "line one\nline two"]]));

        // Escaping must happen before nl2br, or the <br/> tags nl2br inserts get escaped too.
        $this->assertStringContainsString('<br />', $output);
        $this->assertStringContainsString('line one', $output);
        $this->assertStringContainsString('line two', $output);
    }

    #[DataProvider('formatterProvider')]
    public function testSenderNameIsEscaped(object $sut): void
    {
        $output = $sut->format($this->row([
            'createdBy' => [
                'id' => 1,
                'team' => null,
                'loginId' => 'operator',
                'contactDetails' => ['person' => [
                    'forename' => self::PAYLOAD,
                    'familyName' => 'Smith',
                ]],
            ],
        ]));

        $this->assertStringNotContainsString(self::PAYLOAD, $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    #[DataProvider('formatterProvider')]
    public function testSenderLoginIdIsEscaped(object $sut): void
    {
        $output = $sut->format($this->row([
            'createdBy' => [
                'id' => 1,
                'team' => null,
                'loginId' => self::PAYLOAD,
                'contactDetails' => null,
            ],
        ]));

        $this->assertStringNotContainsString(self::PAYLOAD, $output);
    }

    #[DataProvider('formatterProvider')]
    public function testCaseworkerFooterSenderNameIsEscaped(object $sut): void
    {
        // The footer only renders when createdBy has a team, and interpolates the sender name a
        // second time — a separate sink from the {senderName} placeholder.
        $output = $sut->format($this->row([
            'createdBy' => [
                'id' => 1,
                'team' => 'Licensing Team',
                'loginId' => self::PAYLOAD,
                'contactDetails' => null,
            ],
        ]));

        $this->assertStringContainsString('Caseworker Team', $output);
        $this->assertStringNotContainsString(self::PAYLOAD, $output);
    }

    #[DataProvider('formatterProvider')]
    public function testAttachmentDescriptionIsEscaped(object $sut): void
    {
        $output = $sut->format($this->row([
            'documents' => [
                ['id' => 5, 'description' => self::PAYLOAD, 'size' => 1024],
            ],
        ]));

        $this->assertStringNotContainsString(self::PAYLOAD, $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    #[DataProvider('formatterProvider')]
    public function testAttachmentIdCannotBreakOutOfTheHrefAttribute(object $sut): void
    {
        $output = $sut->format($this->row([
            'documents' => [
                ['id' => self::ATTR_PAYLOAD, 'description' => 'a file', 'size' => 1024],
            ],
        ]));

        $this->assertStringNotContainsString('onmouseover="alert(1)"', $output);
        $this->assertStringNotContainsString('href="/file/x" ', $output);
    }

    public function testFirstReadByIsEscaped(): void
    {
        // getFirstReadBy is only rendered by the internal template.
        $output = new InternalConversationMessage()->format($this->row([
            'userMessageReads' => [
                [
                    'createdOn' => '2025-05-16T10:00:00+00:00',
                    'user' => [
                        'id' => 99,
                        'contactDetails' => ['person' => [
                            'forename' => self::PAYLOAD,
                            'familyName' => 'Reader',
                        ]],
                    ],
                ],
            ],
        ]));

        $this->assertStringContainsString('First read by', $output);
        $this->assertStringNotContainsString(self::PAYLOAD, $output);
    }

    /**
     * A valid message row, with the given keys overridden.
     */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'version' => 1,
            'createdOn' => '2025-05-15T10:00:00+00:00',
            'lastModifiedOn' => null,
            'createdBy' => [
                'id' => 1,
                'team' => null,
                'loginId' => 'operator',
                'contactDetails' => null,
            ],
            'lastModifiedBy' => null,
            'messagingContent' => ['id' => 1, 'text' => 'a message', 'version' => 1],
            'documents' => [],
        ], $overrides);
    }
}
