<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Enum\Document;

/**
 * A caseworker's review of a document analysis (document_analysis.assessment_status).
 *
 * Independent of the analysis' own processing status: only a successful analysis can be
 * reviewed, but the review is a human decision, not a pipeline outcome.
 *
 * Lives in olcs-transfer so the API, the internal app and the command's validation share
 * one definition.
 */
enum AssessmentStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    /**
     * The backing values, for places that need plain scalars rather than cases (for example the
     * InArray haystack in a transfer annotation, which cannot hold enum cases).
     */
    public const array VALUES = [
        self::PENDING->value,
        self::APPROVED->value,
        self::REJECTED->value,
    ];
}


