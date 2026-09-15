<?php

namespace Common\Data\Mapper\Continuation;

use Common\Data\Mapper\MapperInterface;

/**
 * OtherFinances
 */
class OtherFinances implements MapperInterface
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
        return [
            'version' => $data['version'],
            'finances' => [
                'yesNo' => $data['hasOtherFinances'] ?? '',
                'yesContent' => [
                    'amount' => $data['otherFinancesAmount'] ?? '',
                    'detail' => $data['otherFinancesDetails'] ?? '',
                ]
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
            'version' => (int)$formData['version'],
            'hasOtherFinances' => $formData['finances']['yesNo'],
            'otherFinancesAmount' => $formData['finances']['yesNo'] === 'Y'
                ? $formData['finances']['yesContent']['amount']
                : null,
            'otherFinancesDetails' => $formData['finances']['yesNo'] === 'Y'
                ? $formData['finances']['yesContent']['detail']
                : null,
        ];
    }
}
