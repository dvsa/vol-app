<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EventBridge\Events;

/**
 * Requests analysis of a financial evidence document.
 *
 * The detail becomes the consuming state machine's execution input verbatim, so the field set
 * is a hard contract - hence the explicit constructor and no generic "extra data" escape hatch.
 */
readonly class FinancialEvidenceDocumentSubmitted implements EventInterface
{
    public const NAME = 'FinancialEvidenceDocumentSubmitted';

    private const SOURCE = 'vol.api';
    private const VERSION = 1;

    private array $detail;

    /**
     * @param array{
     *     organisation_name: string|null,
     *     licence_number: string|null,
     *     nature_of_business: string|null,
     *     business_type: string,
     *     people: list<string>,
     *     application_number: mixed,
     *     trading_name: string,
     *     required_funds: int,
     *     licence_type: string,
     *     application_date: mixed,
     *     vehicles_requested: int
     * } $applicantProfile
     */
    public function __construct(
        string $analysisToken,
        int $applicationId,
        int $volDocumentId,
        string $bucket,
        string $key,
        array $applicantProfile
    ) {
        $this->detail = [
            'version' => (string)self::VERSION,
            'application_id' => $applicationId,
            'analysis_token' => $analysisToken,
            'document' => [
                'vol_document_id' => $volDocumentId,
                'bucket' => $bucket,
                'key' => $key,
            ],
            'applicant_profile' => $applicantProfile,
        ];
    }

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getSource(): string
    {
        return self::SOURCE;
    }

    #[\Override]
    public function getVersion(): int
    {
        return self::VERSION;
    }

    #[\Override]
    public function getDetail(): array
    {
        return $this->detail;
    }
}
