<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\RefData;

class KnowledgeExperience extends AbstractEvidenceMapper
{
    protected const EVIDENCE_FIELD =
        'knowledgeExperienceEvidenceUploaded';

    #[\Override]
    public static function mapFromResult(array $data): array
    {
        $uploadNow = null;
        $uploadLater = null;

        if (
            ($data[self::EVIDENCE_FIELD] ?? null)
            === RefData::AD_UPLOAD_NOW
        ) {
            $uploadNow = RefData::AD_UPLOAD_NOW;
        } elseif (
            ($data[self::EVIDENCE_FIELD] ?? null)
            === RefData::AD_UPLOAD_LATER
        ) {
            $uploadLater = RefData::AD_UPLOAD_LATER;
        }

        return [
            'id' => $data['id'],
            'version' => $data['version'],
            'evidence' => [
                'uploadNowRadio' => $uploadNow,
                'uploadLaterRadio' => $uploadLater,
            ],
            'knowledgeExperienceOlat' =>
                $data['knowledgeExperienceOlat'] ?? null,
        ];
    }

    #[\Override]
    public static function mapFromForm(array $data): array
    {
        $mapped = parent::mapFromForm($data);

        $mapped['knowledgeExperienceOlat'] =
            $data['knowledgeExperienceOlat'] ?? 'N';

        return $mapped;
    }
}