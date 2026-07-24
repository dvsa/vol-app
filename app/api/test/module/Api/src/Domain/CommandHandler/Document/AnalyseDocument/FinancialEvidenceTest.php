<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Aws\EventBridge\EventBridgeClient;
use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument\FinancialEvidence;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Mockery as m;

final class FinancialEvidenceTest extends TestCase
{
    private m\MockInterface $eventBridgeClient;
    private m\MockInterface $documentRepo;
    private FinancialEvidence $sut;

    protected function tearDown(): void
    {
        m::close();
    }

    public function setUp(): void
    {
        $this->eventBridgeClient = m::mock(EventBridgeClient::class);
        $this->documentRepo = m::mock(DocumentRepo::class);

        $sut = new FinancialEvidence(new EventBridge($this->eventBridgeClient));

        $repoManager = m::mock(RepositoryServiceManager::class);
        $repoManager->shouldReceive('get')->with(DocumentRepo::class)->andReturn($this->documentRepo);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn([
            'document_share' => [
                's3' => [
                    'bucket' => 'test-bucket',
                    'key_prefix' => 'prefixed',
                ],
            ],
        ]);
        $container->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($repoManager);
        $container->shouldReceive('get')->with('TransactionManager')->andReturn(m::mock(TransactionManagerInterface::class));
        $container->shouldReceive('get')->with('CommandHandlerManager')->andReturn(m::mock(CommandHandlerManager::class));
        $container->shouldReceive('get')->with('QueryHandlerManager')->andReturn(m::mock(QueryHandlerManager::class));
        $container->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn(m::mock(IdentityProviderInterface::class));

        $this->sut = $sut->__invoke($container, null)->getWrapped();
    }

    public function testHandleCommandFetchesLatestFinancialEvidenceDocumentWhenMissingFromCommand(): void
    {
        $application = m::mock(Application::class);
        $application->shouldReceive('getId')->times(2)->andReturn(1551058);

        $document = m::mock(Document::class);
        $document->shouldReceive('getId')->once()->andReturn(4321);
        $document->shouldReceive('getIdentifier')->once()->andReturn('/folder/financial-evidence.pdf');

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getApplication')->once()->andReturn($application);
        $command->shouldReceive('getDocument')->once()->andReturn(null);

        $this->documentRepo
            ->shouldReceive('fetchLatestFinancialEvidenceForApplication')
            ->with($application)
            ->once()
            ->andReturn($document);

        $this->eventBridgeClient->shouldReceive('putEvents')
            ->once()
            ->with(m::on(function (array $payload): bool {
                $entry = $payload['Entries'][0] ?? null;
                if ($entry === null) {
                    return false;
                }

                $detail = json_decode($entry['Detail'] ?? '', true, 512, JSON_THROW_ON_ERROR);

                return $entry['Source'] === 'olcs.api'
                    && $entry['Version'] === 1
                    && $entry['DetailType'] === \Dvsa\Olcs\Api\Service\EventBridge\Events\AnalyseFinancialEvidenceDocument::class
                    && $entry['Time'] instanceof DateTimeImmutable
                    && $detail === [
                        'document_analysis_token' => 'financial-evidence-1551058-4321',
                        'document' => [
                            'bucket' => 'test-bucket',
                            'key' => 'prefixed/folder/financial-evidence.pdf',
                        ],
                        'applicantProfile' => [
                            'applicationId' => 1551058,
                        ],
                    ];
            }));

        $this->assertNotNull($this->sut->handleCommand($command));
    }

    public function testHandleCommandThrowsWhenNoFinancialEvidenceDocumentExists(): void
    {
        $application = m::mock(Application::class);
        $application->shouldReceive('getId')->once()->andReturn(1551058);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getApplication')->once()->andReturn($application);
        $command->shouldReceive('getDocument')->once()->andReturn(null);

        $this->documentRepo
            ->shouldReceive('fetchLatestFinancialEvidenceForApplication')
            ->with($application)
            ->once()
            ->andReturn(null);

        $this->eventBridgeClient->shouldNotReceive('putEvents');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No financial evidence document found for application 1551058');

        $this->sut->handleCommand($command);
    }
}

