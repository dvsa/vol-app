<?php

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Dvsa\Olcs\Api\Entity;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Addresses implements MapperInterface
{
    protected static $typeMap = [
        'phone_t_primary' => 'phone_primary',
        'phone_t_secondary' => 'phone_secondary',
    ];

    /**
     * Prepare api data for form
     *
     * @param array $data Api data
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $mappedData = [];

        if (!empty($data['correspondenceCd'])) {
            $cdData = $data['correspondenceCd'];

            $mappedData += static::mapContactDetailFromResult('correspondence', $cdData);

            $mappedData['contact'] = static::mapContactsFromResult($cdData);
        }

        //  the consultant data
        if (!empty($data['establishmentCd'])) {
            $mappedData += static::mapContactDetailFromResult('establishment', $data['establishmentCd']);
        }

        //  the consultant data
        if (!empty($data['transportConsultantCd'])) {
            $cdData = $data['transportConsultantCd'];

            $mappedData += [
                'consultant' => [
                    'add-transport-consultant' => 'Y',
                    'writtenPermissionToEngage' => $cdData['writtenPermissionToEngage'],
                    'transportConsultantName' => $cdData['fao'],
                ],
                'consultantAddress' => $cdData['address'],
                'consultantContact' => static::mapContactsFromResult($cdData),
            ];
        }

        return $mappedData;
    }

    /**
     * Prepare form data for Save command
     *
     * @param array $data Form Data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $consultant = array_filter(
            ($data['consultant'] ?? []) +
            [
                'address' => ($data['consultantAddress'] ?? null),
                'contact' => ($data['consultantContact'] ?? null),
            ]
        );

        return array_filter(
            [
                'correspondence' => $data['correspondence'],
                'correspondenceAddress' => $data['correspondence_address'],
                'contact' => ($data['contact'] ?? null),
                'establishment' => ($data['establishment'] ?? null),
                'establishmentAddress' => (
                    $data['establishment_address'] ?? null
                ),
                'consultant' => $consultant,
            ]
        );
    }

    /**
     * Prepare contacts data from API data to Form data
     *
     * @param array $data Api data
     *
     * @return array
     */
    private static function mapContactsFromResult(array $data)
    {
        $contacts = [];

        foreach ($data['phoneContacts'] as $phoneContact) {
            $phoneType = (
                self::$typeMap[$phoneContact['phoneContactType']['id']] ?? ''
            );

            $contacts += [
                $phoneType => $phoneContact['phoneNumber'],
                $phoneType . '_id' => $phoneContact['id'],
                $phoneType . '_version' => $phoneContact['version'],
            ];
        }

        $contacts['email'] = $data['emailAddress'];

        return $contacts;
    }

    /**
     * Prepare common contact details from API data to Form data
     *
     * @param string $type           Contat details type
     * @param array  $contactDetails Api contact details
     *
     * @return array
     */
    private static function mapContactDetailFromResult($type, array $contactDetails)
    {
        $contactDetails['address']['countryCode'] = $contactDetails['address']['countryCode']['id'];

        return [
            $type => [
                'id' => $contactDetails['id'],
                'version' => $contactDetails['version'],
                'fao' => $contactDetails['fao'],
            ],
            $type . '_address' => $contactDetails['address'],
        ];
    }
}
