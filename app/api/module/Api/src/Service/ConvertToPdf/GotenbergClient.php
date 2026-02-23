<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

use Laminas\Http\Client as HttpClient;
use Laminas\Http\Request;
use Dvsa\Olcs\Api\Domain\Exception\RestResponseException;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Laminas\Log\LoggerInterface;

class GotenbergClient implements ConvertToPdfInterface, ConvertHtmlToPdfInterface
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var S3Client|null
     */
    protected $s3Client;

    /**
     * @var string|null
     */
    protected $s3Bucket;

    /**
     * @var string|null
     */
    protected $s3KeyPrefix;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param HttpClient $httpClient Http client to use
     * @param string $baseUri Base URI for Gotenberg service
     * @param S3Client|null $s3Client Optional S3 client for storing PDFs
     * @param string|null $s3Bucket S3 bucket name
     * @param string|null $s3KeyPrefix S3 key prefix (e.g., domain)
     * @param LoggerInterface|null $logger Optional logger
     */
    public function __construct(
        HttpClient $httpClient, 
        string $baseUri,
        ?S3Client $s3Client = null,
        ?string $s3Bucket = null,
        ?string $s3KeyPrefix = null,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->baseUri = rtrim($baseUri, '/');
        $this->s3Client = $s3Client;
        $this->s3Bucket = $s3Bucket;
        $this->s3KeyPrefix = $s3KeyPrefix;
        $this->logger = $logger;
    }

    /**
     * Convert a document to a PDF
     *
     * @param string $fileName    File to be converted
     * @param string $destination Destination file, the PDF file name
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RestResponseException
     */
    public function convert($fileName, $destination)
    {
        $this->httpClient->reset();
        $this->httpClient->setUri($this->baseUri . '/forms/libreoffice/convert');
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setFileUpload($fileName, 'files');

        $response = $this->httpClient->send();
        
        if (!$response->isOk()) {
            $body = $response->getBody();
            $message = $body ?: $response->getReasonPhrase();

            throw new RestResponseException(
                'ConvertToPdf failed, Gotenberg service response : ' . $message,
                $response->getStatusCode()
            );
        }

        $pdfContent = $response->getBody();
        
        // Save to local file system
        file_put_contents($destination, $pdfContent);
        
        // If S3 is configured, also upload to S3
        if ($this->s3Client && $this->s3Bucket) {
            if ($this->logger) {
                $this->logger->info('S3 is configured, attempting upload', [
                    'has_s3_client' => ($this->s3Client !== null),
                    'bucket' => $this->s3Bucket,
                    'key_prefix' => $this->s3KeyPrefix
                ]);
            }
            $this->uploadToS3($fileName, $pdfContent);
        } else {
            if ($this->logger) {
                $this->logger->info('S3 not configured, skipping upload', [
                    'has_s3_client' => ($this->s3Client !== null),
                    'has_bucket' => ($this->s3Bucket !== null),
                    'bucket' => $this->s3Bucket,
                    'key_prefix' => $this->s3KeyPrefix
                ]);
            }
        }
    }

    /**
     * Convert HTML content to PDF using Gotenberg's Chromium endpoint
     *
     * @param string $htmlContent  HTML content to convert
     * @param string $destination  Destination file path for the PDF
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RestResponseException
     */
    public function convertHtml(string $htmlContent, string $destination): void
    {
        $this->httpClient->reset();
        $this->httpClient->setUri($this->baseUri . '/forms/chromium/convert/html');
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setFileUpload('index.html', 'files', $htmlContent, 'text/html');

        $response = $this->httpClient->send();

        if (!$response->isOk()) {
            $body = $response->getBody();
            $message = $body ?: $response->getReasonPhrase();

            throw new RestResponseException(
                'ConvertHtmlToPdf failed, Gotenberg service response : ' . $message,
                $response->getStatusCode()
            );
        }

        $pdfContent = $response->getBody();

        // Save to local file system
        file_put_contents($destination, $pdfContent);
    }

    /**
     * Merge multiple PDF files into one using Gotenberg's PDF engines merge endpoint
     *
     * @param array $pdfFilePaths Array of paths to PDF files to merge
     * @param string $destination Destination file path for the merged PDF
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RestResponseException
     */
    public function mergePdfs(array $pdfFilePaths, string $destination): void
    {
        $this->httpClient->reset();
        $this->httpClient->setUri($this->baseUri . '/forms/pdfengines/merge');
        $this->httpClient->setMethod(Request::METHOD_POST);

        // Gotenberg merges files in alphabetical order by filename,
        // so prefix each with a zero-padded index to preserve input order.
        foreach ($pdfFilePaths as $index => $filePath) {
            $orderedName = sprintf('%03d_%s', $index, basename($filePath));
            $content = file_get_contents($filePath);
            $this->httpClient->setFileUpload($orderedName, 'files', $content, 'application/pdf');
        }

        $response = $this->httpClient->send();

        if (!$response->isOk()) {
            throw new RestResponseException(
                'PDF merge failed: ' . ($response->getBody() ?: $response->getReasonPhrase()),
                $response->getStatusCode()
            );
        }

        file_put_contents($destination, $response->getBody());
    }

    /**
     * Upload PDF to S3
     *
     * @param string $originalFileName Original file name for reference
     * @param string $pdfContent PDF content to upload
     *
     * @return void
     * @throws RestResponseException
     */
    protected function uploadToS3($originalFileName, $pdfContent)
    {
        if ($this->logger) {
            $this->logger->info('uploadToS3 called', [
                'original_file' => $originalFileName,
                'content_size' => strlen($pdfContent)
            ]);
        }
        
        try {
            $date = new DateTime();
            $dateFolder = $date->format('Y-m-d');
            $timestamp = $date->format('YmdHis');
            
            // Sanitize the original filename for S3 key
            $baseName = pathinfo($originalFileName, PATHINFO_FILENAME);
            $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
            $sanitizedName = substr($sanitizedName, 0, 100); // Limit length
            
            // Build S3 key: domain/pdf-conversions/YYYY-MM-DD/timestamp_filename.pdf
            $s3Key = trim($this->s3KeyPrefix, '/') . '/pdf-conversions/' . $dateFolder . '/' 
                   . $timestamp . '_' . $sanitizedName . '.pdf';
            
            $this->s3Client->putObject([
                'Bucket' => $this->s3Bucket,
                'Key' => $s3Key,
                'Body' => $pdfContent,
                'ContentType' => 'application/pdf',
                'Metadata' => [
                    'original-filename' => basename($originalFileName),
                    'conversion-timestamp' => $timestamp,
                ]
            ]);
            
            if ($this->logger) {
                $this->logger->info('PDF uploaded to S3', [
                    'bucket' => $this->s3Bucket,
                    'key' => $s3Key,
                    'original_file' => basename($originalFileName)
                ]);
            }
        } catch (S3Exception $e) {
            // Log the error but don't fail the conversion
            // The PDF is already saved locally, S3 is just for audit/backup
            if ($this->logger) {
                $this->logger->err('Failed to upload PDF to S3: ' . $e->getAwsErrorMessage(), [
                    'bucket' => $this->s3Bucket,
                    'original_file' => basename($originalFileName)
                ]);
            }
        }
    }
}