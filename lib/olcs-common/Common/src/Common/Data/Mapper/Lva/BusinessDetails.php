<?php

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

/**
 * Business Details
 */
class BusinessDetails implements MapperInterface
{
    /**
     * Map data from result data into something for the form
     *
     * @param array $data Data from the API
     *
     * @return array data for the form
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        // Strip out all trading names properties except name
        $tradingNames = [];
        foreach ($data['tradingNames'] as $tradingName) {
            $tradingNames[]['name'] = $tradingName['name'];
        }

        return [
            'version' => $data['version'],
            'data' => [
                'companyNumber' => [
                    'company_number' => $data['companyOrLlpNo']
                ],
                'tradingNames' => $tradingNames,
                'name' => $data['name'],
                'type' => $data['type']['id'],
                'natureOfBusiness' => $data['natureOfBusiness']
            ],
            'registeredAddress' => $data['contactDetails']['address'],
            'allow-email' => [
                'allowEmail' => $data['allowEmail']
            ]
        ];
    }
}
