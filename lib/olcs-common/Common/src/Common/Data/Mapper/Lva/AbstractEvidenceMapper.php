<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\RefData;

/**
 * @todo this was based on the existing FinancialEvidence Mapper - it should be possible to unify these
 */
abstract class AbstractEvidenceMapper implements MapperInterface
{
    protected const EVIDENCE_FIELD = '';

    #[\Override]
    public static function mapFromResult(array $data): array
    {
        $uploadNow = null;
        $uploadLater = null;

        // switch / case do not distinguish 0 and null so need to use this trick
        switch (true) {
            case $data[self::EVIDENCE_FIELD] === RefData::AD_UPLOAD_NOW:
                $uploadNow = RefData::AD_UPLOAD_NOW;
                break;
            case $data[self::EVIDENCE_FIELD] === RefData::AD_UPLOAD_LATER:
                $uploadLater = RefData::AD_UPLOAD_LATER;
                break;
            default:
                $uploadNow = RefData::AD_UPLOAD_NOW;
        }

        $evidenceFieldset = [
            'uploadNowRadio' => $uploadNow,
            'uploadLaterRadio' => $uploadLater,
        ];

        return [
            'id'       => $data['id'],
            'version'  => $data['version'],
            'evidence' => $evidenceFieldset
        ];
    }

    /**
     * Map from post
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromPost(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $uploadNow = null;
        $uploadLater = null;

        $postUploadNow = (int) ($data['evidence']['uploadNow'] ?? 0);
        if ($postUploadNow === RefData::AD_UPLOAD_NOW) {
            $uploadNow = RefData::AD_UPLOAD_NOW;
        } elseif ($postUploadNow === RefData::AD_UPLOAD_LATER) {
            $uploadLater = RefData::AD_UPLOAD_LATER;
        }

        $evidenceFieldset = array_merge(
            $data['evidence'],
            [
                'uploadNowRadio' => $uploadNow,
                'uploadLaterRadio' => $uploadLater,
                'uploadedFileCount' => isset($data['evidence']['files']['list'])
                    ? count($data['evidence']['files']['list'])
                    : 0
            ]
        );
        $data['evidence'] = $evidenceFieldset;

        return $data;
    }

    /**
     * Map from form
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $uploadNow = null;
        $dataEvidence = $data['evidence'];
        if (
            isset($dataEvidence['uploadNowRadio'])
            && (int) $dataEvidence['uploadNowRadio'] === RefData::AD_UPLOAD_NOW
        ) {
            $uploadNow = RefData::AD_UPLOAD_NOW;
        } elseif (
            isset($dataEvidence['uploadLaterRadio'])
            && (int) $dataEvidence['uploadLaterRadio'] === RefData::AD_UPLOAD_LATER
        ) {
            $uploadNow = RefData::AD_UPLOAD_LATER;
        } elseif (isset($dataEvidence['uploadNow'])) {
            $uploadNow = $dataEvidence['uploadNow'];
        }

        return [
            'id' => $data['id'],
            'version' => $data['version'],
            'evidenceUploadType' => $uploadNow
        ];
    }
}
