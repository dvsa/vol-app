<?php

namespace Dvsa\Olcs\Api\Service\EventBridge\Events;

use Dvsa\Olcs\Api\Service\EventBridge\Events\EventInterface;

readonly class AnalyseFinancialEvidenceDocument implements EventInterface
{
    private const SOURCE = 'olcs.api';
    private const VERSION = 1;

    private array $detail;
    public function __construct(string $token, string $bucket, string $key, array $applicantProfile)
    {
        $this->detail = [
            'document_analysis_token' => $token,
            'document' => [
                'bucket' => $bucket,
                'key' => $key,
            ],
            'applicantProfile' => $applicantProfile,
        ];
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }

    public function getVersion(): int
    {
        return self::VERSION;
    }

    public function getDetail(): array
    {
        return $this->detail;
    }
}
