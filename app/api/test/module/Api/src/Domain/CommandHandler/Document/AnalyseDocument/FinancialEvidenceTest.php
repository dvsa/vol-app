<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Aws\EventBridge\EventBridgeClient;
use Doctrine\Common\Collections\ArrayCollection;
use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\Command\Document\AnalyseFinancialEvidence;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument\FinancialEvidence;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Service\Idp\AnalysisTokenGenerator;
use Dvsa\Olcs\Api\Service\Idp\ApplicantProfileBuilder;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Mockery as m;

final class FinancialEvidenceTest extends TestCase
{
    private const APPLICATION_ID = 1551058;

    private const PROFILE = [
        'organisation_name' => 'Test Haulage Ltd',
        'licence_number' => 'OB2014165',
        'nature_of_business' => 'Marketing',
        'business_type' => 'Registered Company',
        'people' => ['Mr John Smith'],
        'application_number' => 1056017,
        'trading_name' => 'Test Haulage',
        'required_funds' => 26000,
        'licence_type' => 'Standard National',
        'application_date' => '2018-01-08',
        'vehicles_requested' => 12,
    ];

    private m\MockInterface $eventBridgeClient;
    private m\MockInterface $documentRepo;
    private m\MockInterface $applicationRepo;
    private m\MockInterface $analysisRepo;
    private FinancialEvidence $sut;

    protected function tearDown(): void
    {
        m::close();
    }

    public function setUp(): void
    {
        $this->eventBridgeClient = m::mock(EventBridgeClient::class);
        $this->documentRepo = m::mock(DocumentRepo::class);
        $this->applicationRepo = m::mock(ApplicationRepo::class);
        $this->analysisRepo = m::mock(DocumentAnalysisRepo::class);

        $this->applicationRepo->shouldReceive('getCategoryReference')->andReturn('cat-ref');
        $this->applicationRepo->shouldReceive('getSubCategoryReference')->andReturn('subcat-ref');

        $profileBuilder = m::mock(ApplicantProfileBuilder::class);
        $profileBuilder->shouldReceive('build')->andReturn(self::PROFILE);

        $sut = new FinancialEvidence(
            new EventBridge($this->eventBridgeClient),
            new AnalysisTokenGenerator(),
            $profileBuilder
        );

        $repoManager = m::mock(RepositoryServiceManager::class);
        $repoManager->shouldReceive('get')->with(DocumentRepo::class)->andReturn($this->documentRepo);
        $repoManager->shouldReceive('get')->with(DocumentAnalysisRepo::class)->andReturn($this->analysisRepo);
        $repoManager->shouldReceive('get')->with('Application')->andReturn($this->applicationRepo);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn([
            'document_share' => [
                's3' => [
                    'bucket' => 'test-bucket',
                    'key_prefix' => 'prefixed',
                ],
            ],
            'idp' => ['dedupe_success_window_hours' => 24],
        ]);
        $container->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($repoManager);
        $container->shouldReceive('get')->with('TransactionManager')->andReturn(m::mock(TransactionManagerInterface::class));
        $container->shouldReceive('get')->with('CommandHandlerManager')->andReturn(m::mock(CommandHandlerManager::class));
        $container->shouldReceive('get')->with('QueryHandlerManager')->andReturn(m::mock(QueryHandlerManager::class));
        $container->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn(m::mock(IdentityProviderInterface::class));

        // Not TransactionedInterface, so __invoke returns the handler itself rather than a
        // TransactioningCommandHandler wrapper.
        $this->sut = $sut->__invoke($container, null);
    }

    public function testEmitsOneEventPerDocumentMatchingTheEventAContract(): void
    {
        $application = $this->givenApplicationWithDocuments([
            [111, '/folder/statement-jan.pdf'],
            [222, '/folder/statement-feb.pdf'],
        ]);

        $this->analysisRepo->shouldReceive('fetchDocumentIdsWithActiveAnalysis')
            ->with([111, 222], m::type(\DateTimeInterface::class))
            ->once()
            ->andReturn([]);

        $this->expectPendingRowCreatedFor([111, 222]);

        $captured = [];
        $this->eventBridgeClient->shouldReceive('putEvents')
            ->twice()
            ->andReturnUsing(function (array $payload) use (&$captured) {
                $captured[] = $payload['Entries'][0];
                return null;
            });

        $this->sut->handleCommand(
            AnalyseFinancialEvidence::create(['application' => self::APPLICATION_ID])
        );

        $this->assertCount(2, $captured, 'one event per document');

        foreach ($captured as $i => $entry) {
            $this->assertSame('vol.api', $entry['Source']);
            $this->assertSame('FinancialEvidenceDocumentSubmitted', $entry['DetailType']);
            $this->assertArrayNotHasKey(
                'Version',
                $entry,
                'Version is not part of the PutEventsRequestEntry schema; it belongs in Detail'
            );
            $this->assertInstanceOf(DateTimeImmutable::class, $entry['Time']);

            $detail = json_decode($entry['Detail'], true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('1', $detail['version']);
            $this->assertSame(self::APPLICATION_ID, $detail['application_id']);
            // assertEquals, not assertSame: a whole-number float round-trips through JSON as
            // an int (94600.0 -> 94600). Still a JSON number, so the contract holds.
            $this->assertEquals(self::PROFILE, $detail['applicant_profile']);
            $this->assertSame(
                [
                    'vol_document_id' => $i === 0 ? 111 : 222,
                    'bucket' => 'test-bucket',
                    'key' => $i === 0 ? 'prefixed/folder/statement-jan.pdf' : 'prefixed/folder/statement-feb.pdf',
                ],
                $detail['document']
            );
            $this->assertMatchesRegularExpression(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
                $detail['analysis_token'],
                'analysis_token must be a UUIDv7'
            );
        }

        $this->assertNotSame(
            $captured[0]['Detail'],
            $captured[1]['Detail'],
            'each document gets its own token'
        );
        unset($application);
    }

    public function testSkipsDocumentsThatAlreadyHaveAnActiveAnalysis(): void
    {
        $this->givenApplicationWithDocuments([
            [111, '/folder/statement-jan.pdf'],
            [222, '/folder/statement-feb.pdf'],
        ]);

        $this->analysisRepo->shouldReceive('fetchDocumentIdsWithActiveAnalysis')
            ->with([111, 222], m::type(\DateTimeInterface::class))
            ->once()
            ->andReturn([111]);

        $this->expectPendingRowCreatedFor([222]);

        $this->eventBridgeClient->shouldReceive('putEvents')->once();

        $result = $this->sut->handleCommand(
            AnalyseFinancialEvidence::create(['application' => self::APPLICATION_ID])
        );

        $this->assertContains('Document 111 already has an active analysis; skipped', $result->getMessages());
    }

    /** A failed emit must leave a PENDING row for the sweeper, not lose the work. */
    public function testLeavesRowPendingAndDoesNotThrowWhenTheEmitFails(): void
    {
        $this->givenApplicationWithDocuments([[111, '/folder/statement-jan.pdf']]);

        $this->analysisRepo->shouldReceive('fetchDocumentIdsWithActiveAnalysis')->once()->andReturn([]);
        $this->expectPendingRowCreatedFor([111]);

        $this->eventBridgeClient->shouldReceive('putEvents')
            ->once()
            ->andThrow(new \RuntimeException('EventBridge unavailable'));

        $result = $this->sut->handleCommand(
            AnalyseFinancialEvidence::create(['application' => self::APPLICATION_ID])
        );

        $this->assertContains(
            'Analysis event emit failed for document 111; left pending',
            $result->getMessages()
        );
    }

    public function testDoesNothingWhenTheApplicationHasNoFinancialEvidence(): void
    {
        $this->givenApplicationWithDocuments([]);

        $this->analysisRepo->shouldNotReceive('createPending');
        $this->eventBridgeClient->shouldNotReceive('putEvents');

        $result = $this->sut->handleCommand(
            AnalyseFinancialEvidence::create(['application' => self::APPLICATION_ID])
        );

        $this->assertContains(
            'No financial evidence documents found for application 1551058',
            $result->getMessages()
        );
    }

    public function testUsesTheDocumentNamedByTheCommandWhenSupplied(): void
    {
        $application = m::mock(Application::class);
        $application->shouldReceive('getId')->andReturn(self::APPLICATION_ID);
        $application->shouldNotReceive('getApplicationDocuments');

        $this->applicationRepo->shouldReceive('fetchById')
            ->with(self::APPLICATION_ID)->once()->andReturn($application);

        $document = $this->mockDocument(999, '/folder/named.pdf');
        $this->documentRepo->shouldReceive('fetchById')->with(999)->once()->andReturn($document);

        $this->analysisRepo->shouldReceive('fetchDocumentIdsWithActiveAnalysis')
            ->with([999], m::type(\DateTimeInterface::class))->once()->andReturn([]);
        $this->expectPendingRowCreatedFor([999]);

        $this->eventBridgeClient->shouldReceive('putEvents')->once();

        $result = $this->sut->handleCommand(AnalyseFinancialEvidence::create([
            'application' => self::APPLICATION_ID,
            'document' => 999,
        ]));

        $this->assertContains('Analysis requested for document 999', $result->getMessages());
    }

    /**
     * @param array<int, array{0: int, 1: string}> $documents
     */
    private function givenApplicationWithDocuments(array $documents): m\MockInterface
    {
        $collection = new ArrayCollection(
            array_map(fn(array $d): m\MockInterface => $this->mockDocument($d[0], $d[1]), $documents)
        );

        $application = m::mock(Application::class);
        $application->shouldReceive('getId')->andReturn(self::APPLICATION_ID);
        $application->shouldReceive('getApplicationDocuments')
            ->with('cat-ref', 'subcat-ref')
            ->once()
            ->andReturn($collection);

        $this->applicationRepo->shouldReceive('fetchById')
            ->with(self::APPLICATION_ID)
            ->once()
            ->andReturn($application);

        return $application;
    }

    private function mockDocument(int $id, string $identifier): m\MockInterface
    {
        $document = m::mock(Document::class);
        $document->shouldReceive('getId')->andReturn($id);
        $document->shouldReceive('getIdentifier')->andReturn($identifier);

        return $document;
    }

    /**
     * @param int[] $documentIds
     */
    private function expectPendingRowCreatedFor(array $documentIds): void
    {
        foreach ($documentIds as $documentId) {
            $analysis = m::mock(DocumentAnalysis::class);
            $analysis->shouldReceive('getId')->andReturn($documentId * 10);

            $this->analysisRepo->shouldReceive('createPending')
                ->once()
                ->with(
                    m::on(static fn(string $token): bool => strlen($token) === 16),
                    m::type(Application::class),
                    m::on(static fn($document): bool => (int)$document->getId() === $documentId)
                )
                ->andReturn($analysis);
        }
    }
}
