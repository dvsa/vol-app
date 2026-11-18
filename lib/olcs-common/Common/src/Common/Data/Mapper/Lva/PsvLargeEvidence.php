<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

class PsvLargeEvidence extends AbstractEvidenceMapper
{
    protected const EVIDENCE_FIELD = 'occupationEvidenceUploaded';

    #[\Override]
    public static function mapFromPost(array $data): array
    {
        return self::mapFromPostAsSingleChoice($data);
    }
}
