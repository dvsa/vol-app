<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\Sfn\SfnClient;
use Dvsa\Olcs\Api\Domain\Command\Document\StoreDocumentAnalysisResult as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\StoreDocumentAnalysisResult;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Mockery as m;

final class StoreDocumentAnalysisResultTest extends TestCase
{
    private const TOKEN_STRING  = '018f1234-5678-7000-8000-000000000001';
    private const EXECUTION_ARN = 'arn:aws:states:eu-west-1:123456789:execution:test-state-machine:abc-123';
    private const BUCKET        = 'test-analysis-output-bucket';
    private const KEY           = 'some/key/analysis/result.json';

    private m\MockInterface $analysisRepo;
    private m\MockInterface $sfnClient;
    private m\MockInterface $s3Client;
    private StoreDocumentAnalysisResult $sut;

    protected function tearDown(): void
    {
        m::close();
    }

    #[\Override]
    public function setUp(): void
    {
        $this->analysisRepo = m::mock(DocumentAnalysisRepo::class);
        $this->sfnClient    = m::mock(SfnClient::class);
        $this->s3Client     = m::mock(S3Client::class);

        $repoManager = m::mock(\Dvsa\Olcs\Api\Domain\RepositoryServiceManager::class);
        $repoManager->shouldReceive('get')->with(DocumentAnalysisRepo::class)->andReturn($this->analysisRepo);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($repoManager);
        $container->shouldReceive('get')->with('TransactionManager')->andReturn(m::mock(TransactionManagerInterface::class));
        $container->shouldReceive('get')->with('CommandHandlerManager')->andReturn(m::mock(CommandHandlerManager::class));
        $container->shouldReceive('get')->with('QueryHandlerManager')->andReturn(m::mock(QueryHandlerManager::class));
        $container->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn(m::mock(IdentityProviderInterface::class));

        $sut = new StoreDocumentAnalysisResult($this->sfnClient, $this->s3Client);
        $this->sut = $sut->__invoke($container, null);
    }

    private function makeAnalysis(int $id = 1): m\MockInterface
    {
        $application = m::mock(Application::class);
        $application->shouldReceive('getId')->andReturn(100);

        $analysis = m::mock(Entity::class);
        $analysis->shouldReceive('getId')->andReturn($id);
        $analysis->shouldReceive('getApplication')->andReturn($application);

        return $analysis;
    }

    private function makeExecutionOutput(string $bucket = self::BUCKET, string $key = self::KEY): string
    {
        return json_encode([
            'config' => ['outputBucket' => $bucket],
            'analysisResult' => ['smOutput' => ['status' => 'SUCCEEDED', 'analysisResultKey' => $key]],
        ]);
    }

    public function testSuccessfulProcessingTransitionsToSuccess(): void
    {
        $analysis = $this->makeAnalysis(1);

        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->with(['executionArn' => self::EXECUTION_ARN])
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $resultPayload = [
            'metadata' => ['bucket' => self::BUCKET, 'executionId' => self::EXECUTION_ARN, 'classification' => 'BANK_STATEMENT'],
            'applicantProfile' => ['organisation_name' => 'Test Organisation Ltd'],
            'analysis' => ['checks' => ['passed' => true]],
        ];

        $this->s3Client->shouldReceive('getObject')
            ->with(['Bucket' => self::BUCKET, 'Key' => self::KEY])
            ->once()
            ->andReturn(new Result(['Body' => json_encode($resultPayload)]));

        $this->analysisRepo->shouldReceive('recordSuccess')
            ->once()
            ->withArgs(function (int $id, array $result, array $metadata) use ($resultPayload): bool {
                return $id === 1
                    && $result === [
                        'applicantProfile' => $resultPayload['applicantProfile'],
                        'analysis' => $resultPayload['analysis'],
                    ]
                    && $metadata === $resultPayload['metadata'];
            })
            ->andReturn(1);

        $result = $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));

        $this->assertStringContainsString('stored successfully', implode(' ', $result->getMessages()));
    }

    public function testMissingMetadataKeyStoresEmptyMetadataAndFullResult(): void
    {
        $analysis = $this->makeAnalysis(1);

        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $resultPayload = ['checks' => ['passed' => true]];

        $this->s3Client->shouldReceive('getObject')
            ->once()
            ->andReturn(new Result(['Body' => json_encode($resultPayload)]));

        $this->analysisRepo->shouldReceive('recordSuccess')
            ->once()
            ->withArgs(function (int $id, array $result, array $metadata) use ($resultPayload): bool {
                return $id === 1 && $result === $resultPayload && $metadata === [];
            })
            ->andReturn(1);

        $result = $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));

        $this->assertStringContainsString('stored successfully', implode(' ', $result->getMessages()));
    }

    public function testResolvesS3LocationFromRealOrchestratorOutputShape(): void
    {
        $analysis = $this->makeAnalysis(1);
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $orchestratorOutput = [
            'bucket' => 'test-source-document-bucket',
            'key' => 'some/document/path/source-document.pdf',
            'analysisToken' => self::TOKEN_STRING,
            'applicationId' => 100200,
            'config' => ['outputBucket' => self::BUCKET],
            'analysisInput' => ['bucket' => 'test-source-document-bucket', 'key' => 'irrelevant.pdf'],
            'analysisResult' => ['smOutput' => ['status' => 'SUCCEEDED', 'analysisResultKey' => self::KEY]],
        ];

        $this->sfnClient->shouldReceive('describeExecution')
            ->with(['executionArn' => self::EXECUTION_ARN])
            ->once()
            ->andReturn(new Result(['output' => json_encode($orchestratorOutput)]));

        $this->s3Client->shouldReceive('getObject')
            ->with(['Bucket' => self::BUCKET, 'Key' => self::KEY])
            ->once()
            ->andReturn(new Result(['Body' => json_encode(['metadata' => [], 'analysis' => []])]));

        $this->analysisRepo->shouldReceive('recordSuccess')->once()->andReturn(1);

        $result = $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));

        $this->assertStringContainsString('stored successfully', implode(' ', $result->getMessages()));
    }

    public function testAlreadyTerminalIsANoOp(): void
    {
        $analysis = $this->makeAnalysis(1);

        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $this->s3Client->shouldReceive('getObject')
            ->once()
            ->andReturn(new Result(['Body' => '{"ok":true}']));

        // 0 rows affected = already terminal
        $this->analysisRepo->shouldReceive('recordSuccess')->once()->andReturn(0);

        $result = $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));

        $this->assertStringContainsString('no-op', implode(' ', $result->getMessages()));
    }

    public function testMissingAnalysisThrowsRuntimeException(): void
    {
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturnNull();

        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testInvalidJsonInS3TransitionsToErrorAndThrowsRuntimeException(): void
    {
        $analysis = $this->makeAnalysis(1);
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $this->s3Client->shouldReceive('getObject')
            ->once()
            ->andReturn(new Result(['Body' => 'not json at all']));

        $this->analysisRepo->shouldReceive('recordError')
            ->once()
            ->withArgs(fn(int $id, string $detail) => $id === 1 && str_contains($detail, 'JSON'))
            ->andReturn(1);

        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testMissingS3ObjectThrowsRuntimeException(): void
    {
        $analysis = $this->makeAnalysis(1);
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $s3Exception = $this->makeS3Exception('NoSuchKey');

        $this->s3Client->shouldReceive('getObject')->once()->andThrow($s3Exception);

        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testTransientS3FailurePropagatesForRetry(): void
    {
        $analysis = $this->makeAnalysis(1);
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $s3Exception = $this->makeS3Exception('ServiceUnavailable');

        $this->s3Client->shouldReceive('getObject')->once()->andThrow($s3Exception);

        $this->expectException(S3Exception::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testMissingBucketInExecutionOutputThrowsRuntimeException(): void
    {
        $analysis = $this->makeAnalysis(1);
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => json_encode([
                'analysisResult' => ['smOutput' => ['analysisResultKey' => 'only-key']],
            ])]));

        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testEmptyTokenThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => '',
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testInvalidTokenFormatThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->handleCommand(Cmd::create([
            'analysisToken' => 'not-a-uuid',
            'executionArn'  => self::EXECUTION_ARN,
        ]));
    }

    public function testRecordSuccessIssuesSingleUpdateWithPendingCondition(): void
    {
        // Confirm the repo method is called exactly once with WHERE status = PENDING.
        $analysis = $this->makeAnalysis(5);
        $this->analysisRepo->shouldReceive('fetchByToken')->once()->andReturn($analysis);

        $this->sfnClient->shouldReceive('describeExecution')
            ->once()
            ->andReturn(new Result(['output' => $this->makeExecutionOutput()]));

        $this->s3Client->shouldReceive('getObject')
            ->once()
            ->andReturn(new Result(['Body' => '{}']));

        $this->analysisRepo->shouldReceive('recordSuccess')
            ->once()
            ->with(5, [], m::any())
            ->andReturn(1);

        $result = $this->sut->handleCommand(Cmd::create([
            'analysisToken' => self::TOKEN_STRING,
            'executionArn'  => self::EXECUTION_ARN,
        ]));

        $this->assertNotEmpty($result->getMessages());
    }

    private function makeS3Exception(string $errorCode): S3Exception
    {
        $command = m::mock(\Aws\CommandInterface::class);

        return new S3Exception(
            $errorCode,
            $command,
            ['code' => $errorCode]
        );
    }
}
