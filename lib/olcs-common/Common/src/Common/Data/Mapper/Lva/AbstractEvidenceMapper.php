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
        $evidenceUploadType = filter_var(
            $data[static::EVIDENCE_FIELD] ?? null,
            FILTER_VALIDATE_INT
        );
        $uploadChoice = in_array(
            $evidenceUploadType,
            [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_LATER],
            true
        ) ? $evidenceUploadType : null;

        return [
            'id'       => $data['id'],
            'version'  => $data['version'],
            'evidence' => [
                'uploadNowRadio' => $uploadChoice,
            ],
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

    protected static function mapFromPostAsSingleChoice(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $postUploadChoice = filter_var(
            $data['evidence']['uploadNow'] ?? null,
            FILTER_VALIDATE_INT
        );
        $uploadChoice = in_array(
            $postUploadChoice,
            [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_LATER],
            true
        ) ? $postUploadChoice : null;

        $data['evidence'] = array_merge(
            $data['evidence'],
            [
                'uploadNowRadio' => $uploadChoice,
                'uploadedFileCount' => isset($data['evidence']['files']['list'])
                    ? count($data['evidence']['files']['list'])
                    : 0,
            ]
        );

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
            && in_array(
                (int) $dataEvidence['uploadNowRadio'],
                [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_LATER],
                true
            )
        ) {
            $uploadNow = (int) $dataEvidence['uploadNowRadio'];
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
