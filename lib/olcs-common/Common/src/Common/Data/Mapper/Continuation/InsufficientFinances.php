<?php

namespace Common\Data\Mapper\Continuation;

use Common\Data\Mapper\MapperInterface;

/**
 * InsufficientFinances
 */
class InsufficientFinances implements MapperInterface
{
    /**
     * Map data from API data into something for the form
     *
     * @param array $data Data from the API
     *
     * @return array data for the form
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $uploadSendValue = $data['financialEvidenceUploaded'] === false ? 'send' : 'upload';
        return [
            'version' => $data['version'],
            'insufficientFinances' => [
                'yesNo' => $data['financialEvidenceUploaded'] !== null ? 'Y' : null,
                'yesContent' => [
                    'radio' => $data['financialEvidenceUploaded'] === null ? null : $uploadSendValue
                ],
            ]
        ];
    }

    /**
     * Map data from form to DTO
     *
     * @param array $formData Form data
     *
     * @return array
     */
    public static function mapFromForm(array $formData)
    {
        return [
            'version' => $formData['version'],
            'financialEvidenceUploaded' =>
                $formData['insufficientFinances']['yesContent']['radio'] === 'upload',
        ];
    }
}
