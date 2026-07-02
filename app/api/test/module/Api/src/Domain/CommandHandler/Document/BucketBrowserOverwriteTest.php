<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\BucketBrowserOverwrite;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\Transfer\Command\Document\BucketBrowserOverwrite as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\Http\Response;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use RuntimeException;

class BucketBrowserOverwriteTest extends AbstractCommandHandlerTestCase
{
    /** @var m\MockInterface|S3BucketBrowser */
    private $mockBrowser;

    public function setUp(): void
    {
        $this->sut = new BucketBrowserOverwrite();
        $this->mockBrowser = m::mock(S3BucketBrowser::class);

        $this->mockedSmServices = [
            S3BucketBrowser::class => $this->mockBrowser,
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser->getId')->andReturn(1);

        parent::setUp();
    }

    private function okResponse(int $code): Response
    {
        $response = new Response();
        $response->setStatusCode($code);
        return $response;
    }

    public function testHandleCommandOverwritesObject(): void
    {
        $this->mockBrowser->shouldReceive('putObject')
            ->with('documents/x.pdf', m::type(File::class))
            ->once()
            ->andReturn($this->okResponse(200));

        $result = $this->sut->handleCommand(Cmd::create([
            'key' => 'documents/x.pdf',
            'content' => base64_encode('new-bytes'),
        ]));

        $this->assertContains('Object overwritten', $result->toArray()['messages']);
        $this->assertSame(['key' => 'documents/x.pdf'], $result->toArray()['id']);
    }

    public function testHandleCommandThrowsWhenStoreFails(): void
    {
        $this->mockBrowser->shouldReceive('putObject')
            ->with('documents/x.pdf', m::type(File::class))
            ->once()
            ->andReturn($this->okResponse(500));

        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'key' => 'documents/x.pdf',
            'content' => base64_encode('new-bytes'),
        ]));
    }

    public function testHandleCommandRejectsInvalidBase64Content(): void
    {
        // Invalid base64 must NOT silently write corrupted/empty bytes over the object.
        $this->mockBrowser->shouldReceive('putObject')->never();

        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'key' => 'documents/x.pdf',
            'content' => '@@not-valid-base64@@',
        ]));
    }
}
