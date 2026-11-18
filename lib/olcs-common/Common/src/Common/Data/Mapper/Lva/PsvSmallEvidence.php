<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

class PsvSmallEvidence extends AbstractEvidenceMapper
{
    protected const EVIDENCE_FIELD = 'smallVehicleEvidenceUploaded';

    #[\Override]
    public static function mapFromPost(array $data): array
    {
        return self::mapFromPostAsSingleChoice($data);
    }
}
