<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\View;

use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Renders the printable-message partial and asserts it escapes.
 *
 * The snapshot is stored as .html and served inline, so anything unescaped here executes in the
 * viewer's session when they open the document.
 */
final class PrintableMessagePartialTest extends MockeryTestCase
{
    private const PAYLOAD = '<script>alert(document.domain)</script>';

    private PhpRenderer $renderer;

    #[\Override]
    protected function setUp(): void
    {
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../../../../module/Snapshot/view');

        $this->renderer = new PhpRenderer();
        $this->renderer->setResolver($resolver);
    }

    public function testMessageBodyIsEscaped(): void
    {
        $output = $this->render(body: self::PAYLOAD);

        $this->assertStringNotContainsString(self::PAYLOAD, $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testMessageBodyStillConvertsNewlinesToBreaks(): void
    {
        // Escaping must precede nl2br, or the inserted <br /> tags are escaped too.
        $output = $this->render(body: "line one\nline two");

        $this->assertStringContainsString('<br />', $output);
        $this->assertStringContainsString('line one', $output);
    }

    public function testSenderNameIsEscaped(): void
    {
        $output = $this->render(loginId: self::PAYLOAD);

        $this->assertStringNotContainsString(self::PAYLOAD, $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testCaseworkerFooterSenderNameIsEscaped(): void
    {
        // The footer renders only when the sender has a team, and echoes the name a second time.
        $output = $this->render(loginId: self::PAYLOAD, team: 'Licensing Team');

        $this->assertStringContainsString('Caseworker Team', $output);
        $this->assertStringNotContainsString(self::PAYLOAD, $output);
    }

    public function testReadByNameIsEscaped(): void
    {
        $output = $this->render(enhanced: true, readByLoginId: self::PAYLOAD);

        $this->assertStringContainsString('Read by', $output);
        $this->assertStringNotContainsString(self::PAYLOAD, $output);
    }

    private function render(
        string $body = 'a message',
        string $loginId = 'operator',
        ?string $team = null,
        bool $enhanced = false,
        ?string $readByLoginId = null,
    ): string {
        $createdOn = new \DateTime('2025-05-15T10:00:00+00:00');

        $createdBy = m::mock();
        $createdBy->allows('getContactDetails')->withNoArgs()->andReturns(null);
        $createdBy->allows('getTeam')->withNoArgs()->andReturns($team);
        $createdBy->allows('getLoginId')->withNoArgs()->andReturns($loginId);

        $content = m::mock();
        $content->allows('getText')->withNoArgs()->andReturns($body);

        $reads = [];
        if ($readByLoginId !== null) {
            $readUser = m::mock();
            $readUser->allows('getContactDetails')->withNoArgs()->andReturns(null);
            $readUser->allows('getLoginId')->withNoArgs()->andReturns($readByLoginId);

            $read = m::mock();
            $read->allows('getUser')->withNoArgs()->andReturns($readUser);
            $read->allows('getCreatedOn')->with(true)->andReturns(clone $createdOn);

            $reads[] = $read;
        }

        $message = m::mock();
        $message->allows('getCreatedBy')->withNoArgs()->andReturns($createdBy);
        $message->allows('getMessagingContent')->withNoArgs()->andReturns($content);
        $message->allows('getCreatedOn')->with(true)->andReturns(clone $createdOn);
        $message->allows('getUserMessageReads')->withNoArgs()->andReturns($reads);

        return $this->renderer->render(
            'partials/read-only/printable-message',
            ['message' => $message, 'enhanced' => $enhanced],
        );
    }
}
