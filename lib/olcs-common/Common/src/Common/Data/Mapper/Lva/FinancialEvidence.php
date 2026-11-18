<?php

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\Form;
use Common\RefData;

/**
 * Financial Evidence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FinancialEvidence implements MapperInterface
{
    /**
     * Map from result
     *
     * @param array $data data
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $uploadChoice = in_array($data['financialEvidenceUploaded'], [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_LATER], true)
            ? $data['financialEvidenceUploaded']
            : null;

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
    public static function mapFromPost(array $data)
    {
        if ($data === []) {
            return [];
        }

        $uploadChoice = null;
        $sendByPost = null;

        $postUploadNow = (int) ($data['evidence']['uploadNow'] ?? 0);
        if (in_array($postUploadNow, [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_LATER], true)) {
            $uploadChoice = $postUploadNow;
        } elseif ($postUploadNow === RefData::AD_POST) {
            $sendByPost = RefData::AD_POST;
        }

        $evidenceFieldset = array_merge(
            $data['evidence'],
            [
                'uploadNowRadio' => $uploadChoice,
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
    public static function mapFromForm(array $data)
    {
        $uploadNow = null;
        $dataEvidence = $data['evidence'];
        if (
            isset($dataEvidence['uploadNowRadio'])
            && in_array((int) $dataEvidence['uploadNowRadio'], [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_LATER], true)
        ) {
            $uploadNow = (int) $dataEvidence['uploadNowRadio'];
        } elseif (
            isset($dataEvidence['uploadLaterRadio'])
            && (int) $dataEvidence['uploadLaterRadio'] === RefData::AD_UPLOAD_LATER
        ) {
            $uploadNow = RefData::AD_UPLOAD_LATER;
        }

        return [
            'id' => $data['id'],
            'version' => $data['version'],
            'financialEvidenceUploaded' => $uploadNow
        ];
    }
}
