<?php

namespace Dvsa\Olcs\Api\Service\Ebsr;

use Aws\S3\S3Client;

class S3Processor implements EbsrProcessingInterface
{
    private S3Client $s3Client;

    private string $bucketName;

    public function __construct(S3Client $s3Client, string $bucketName)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;
    }

    public function process(string $identifier, array $options = []): string
    {
        $result = $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' =>  $options['s3Filename'] ?? basename($identifier),
            'Body' => file_get_contents($identifier)
        ]);

        return $result['ObjectURL'];
    }
}
