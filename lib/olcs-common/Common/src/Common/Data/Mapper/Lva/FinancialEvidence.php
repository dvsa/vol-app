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
        $uploadNow = null;
        $uploadLater = null;
        $sendByPost = null;

        // switch / case do not distinguishes 0 and null so need to use this trick
        switch (true) {
            case $data['financialEvidenceUploaded'] === RefData::AD_UPLOAD_NOW:
                $uploadNow = RefData::AD_UPLOAD_NOW;
                break;
            case $data['financialEvidenceUploaded'] === RefData::AD_POST:
                $sendByPost = RefData::AD_POST;
                break;
            case $data['financialEvidenceUploaded'] === RefData::AD_UPLOAD_LATER:
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
    public static function mapFromPost(array $data)
    {
        if ($data === []) {
            return [];
        }

        $uploadNow = null;
        $uploadLater = null;
        $sendByPost = null;

        $postUploadNow = (int) ($data['evidence']['uploadNow'] ?? 0);
        if ($postUploadNow === RefData::AD_UPLOAD_NOW) {
            $uploadNow = RefData::AD_UPLOAD_NOW;
        } elseif ($postUploadNow === RefData::AD_POST) {
            $sendByPost = RefData::AD_POST;
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
    public static function mapFromForm(array $data)
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
        }

        return [
            'id' => $data['id'],
            'version' => $data['version'],
            'financialEvidenceUploaded' => $uploadNow
        ];
    }
}
