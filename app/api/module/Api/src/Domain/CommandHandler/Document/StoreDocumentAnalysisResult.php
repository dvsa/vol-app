<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\Sfn\SfnClient;
use Dvsa\Olcs\Api\Domain\Command\Document\StoreDocumentAnalysisResult as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Uid\Uuid;

/**
 * Retrieves the document analysis bucket and key from Step Functions execution output,
 * then transitions the document_analysis row from PENDING to SUCCESS or ERROR.
 *
 * The S3 result payload has the shape {"metadata": {...}, "applicantProfile": {...},
 * "analysis": {...}}. The "metadata" key (pipeline/provider facts such as bucket,
 * executionId, classification) is stored in result_metadata; everything else
 * (applicantProfile, analysis) is stored as-is in result.
 *
 * All failures will retry at least once, which is globally set in terraform for all AWS Batch Jobs.
 *
 * The result processor and the timeout sweeper race on the same rows. Both use single
 * conditional UPDATEs (WHERE status = 'PENDING'), so neither can overwrite a terminal state.
 */
final class StoreDocumentAnalysisResult extends AbstractCommandHandler
{
    protected $repoServiceName = Repository\DocumentAnalysis::class;

    public function __construct(
        private readonly SfnClient $sfnClient,
        private readonly S3Client $s3Client,
    ) {
    }

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        if (!$command instanceof Cmd) {
            throw new RuntimeException(sprintf('%s cannot handle %s', static::class, $command::class));
        }

        $tokenString  = $command->getAnalysisToken();
        $executionArn = $command->getExecutionArn();

        if ($tokenString === '' || $executionArn === '') {
            throw new RuntimeException(
                'analysis_token and execution_arn are required'
            );
        }

        /** @var Repository\DocumentAnalysis $repo */
        $repo = $this->getRepo();

        $analysis = $repo->fetchByToken($this->tokenStringToBinary($tokenString));

        if ($analysis === null) {
            throw new RuntimeException(sprintf(
                'No document_analysis row for token %s',
                $tokenString
            ));
        }

        $analysisId    = (int) $analysis->getId();
        $applicationId = $analysis->getApplication()?->getId();

        Logger::info('IDP store-document-analysis-result: starting', [
            'analysis_token' => $tokenString,
            'analysis_id'    => $analysisId,
            'application_id' => $applicationId,
            'execution_arn'  => $executionArn,
        ]);

        [$bucket, $key] = $this->resolveS3LocationFromExecution($executionArn);

        $resultJson = $this->fetchS3Object($bucket, $key, $tokenString, $analysisId, $applicationId, $executionArn);

        $decoded = json_decode($resultJson, true);

        if (!is_array($decoded)) {
            $this->attemptRecordError(
                $repo,
                $analysisId,
                'S3 result is not valid JSON',
                $tokenString,
                $applicationId
            );

            throw new RuntimeException(
                sprintf('S3 result at s3://%s/%s is not valid JSON', $bucket, $key)
            );
        }

        $metadata = $decoded['metadata'] ?? [];

        if (!is_array($metadata)) {
            $metadata = [];
        }

        $result = $decoded;
        unset($result['metadata']);

        $affected = $repo->recordSuccess($analysisId, $result, $metadata);

        if ($affected === 0) {
            Logger::info('IDP store-document-analysis-result: already terminal, skipping', [
                'analysis_token' => $tokenString,
                'analysis_id'    => $analysisId,
                'application_id' => $applicationId,
            ]);

            $this->result->addMessage('Analysis already in terminal state; no-op');

            return $this->result;
        }

        Logger::info('IDP store-document-analysis-result: stored successfully', [
            'analysis_token' => $tokenString,
            'analysis_id'    => $analysisId,
            'application_id' => $applicationId,
            'execution_arn'  => $executionArn,
            's3_bucket'      => $bucket,
            's3_key'         => $key,
        ]);

        $this->result->addMessage(sprintf('Analysis %d stored successfully', $analysisId));

        return $this->result;
    }

    /**
     * Calls sfn:DescribeExecution to get the execution output and extracts the S3 location.
     *
     * @return array{string, string} [bucket, key]
     */
    private function resolveS3LocationFromExecution(string $executionArn): array
    {
        $response = $this->sfnClient->describeExecution(['executionArn' => $executionArn]);

        $rawOutput = $response->get('output');

        if (!is_string($rawOutput) || $rawOutput === '') {
            throw new RuntimeException(
                'Step Functions execution has no output; pipeline may still be running or failed'
            );
        }

        $output = json_decode($rawOutput, true);

        if (!is_array($output)) {
            throw new RuntimeException(
                'Step Functions execution output is not valid JSON'
            );
        }

        $bucket = $output['config']['outputBucket'] ?? null;
        $key    = $output['analysisResult']['smOutput']['analysisResultKey'] ?? null;

        if (!is_string($bucket) || $bucket === '' || !is_string($key) || $key === '') {
            throw new RuntimeException(
                'Step Functions execution output missing config.outputBucket or analysisResult.smOutput.analysisResultKey'
            );
        }

        return [$bucket, $key];
    }

    private function fetchS3Object(
        string $bucket,
        string $key,
        string $tokenString,
        int $analysisId,
        mixed $applicationId,
        string $executionArn
    ): string {
        try {
            $response = $this->s3Client->getObject(['Bucket' => $bucket, 'Key' => $key]);

            return (string) $response->get('Body');
        } catch (S3Exception $e) {
            $errorCode = $e->getAwsErrorCode();

            Logger::err('IDP store-document-analysis-result: S3 fetch failed', [
                'analysis_token' => $tokenString,
                'analysis_id'    => $analysisId,
                'application_id' => $applicationId,
                'execution_arn'  => $executionArn,
                's3_bucket'      => $bucket,
                's3_key'         => $key,
                'aws_error_code' => $errorCode,
            ]);

            if (in_array($errorCode, ['NoSuchKey', 'NoSuchBucket'], true)) {
                throw new RuntimeException(
                    sprintf('S3 object not found: s3://%s/%s (%s)', $bucket, $key, $errorCode)
                );
            }

            throw $e;
        }
    }

    /**
     * Best-effort PENDING -> ERROR transition before re-throwing the failure.
     * Logs if the transition does not affect any rows (already terminal).
     */
    private function attemptRecordError(
        Repository\DocumentAnalysis $repo,
        int $analysisId,
        string $errorDetail,
        string $tokenString,
        mixed $applicationId
    ): void {
        $affected = $repo->recordError($analysisId, $errorDetail);

        if ($affected === 0) {
            Logger::info('IDP store-document-analysis-result: ERROR transition was a no-op (already terminal)', [
                'analysis_token' => $tokenString,
                'analysis_id'    => $analysisId,
                'application_id' => $applicationId,
            ]);
        }
    }

    private function tokenStringToBinary(string $tokenString): string
    {
        try {
            return Uuid::fromRfc4122($tokenString)->toBinary();
        } catch (\Throwable) {
            throw new RuntimeException(
                sprintf('Invalid analysis_token format: %s', $tokenString)
            );
        }
    }
}
