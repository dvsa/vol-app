<?php

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use DateTime;
use Dvsa\Olcs\Transfer\Command\Licence\CreateOperatingCentre;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

class CreateOperatingCentreTest extends TestCase
{
    use CommandTest;

    /**
     * {@inheritdoc}
     */
    protected function createBlankDto()
    {
        return new CreateOperatingCentre();
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptionalDtoFields()
    {
        return [
            'taIsOverridden',
            'address',
            'noOfTrailersRequired',
            'permission',
            'adPlaced',
            'adPlacedIn',
            'adPlacedDate'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidFieldValues()
    {
        $now = new DateTime();
        $yesterday = new DateTime('yesterday');
        $format = 'Y-m-d';

        return [
            'taIsOverridden' => ['Y', 'N'],
            'licence' => ['1', '2'],
            'noOfVehiclesRequired' => ['1', '1000000'],
            'noOfTrailersRequired' => ['1', '1000000'],
            'address' => [
                [
                    'id' => null,
                    'version' => null,
                    'addressLine1' => 'test',
                    'addressLine2' => null,
                    'addressLine3' => null,
                    'addressLine4' => null,
                    'town' => 'test',
                    'postcode' => null,
                    'countryCode' => 'GB',
                ]
            ],
            'permission' => ['Y', 'N'],
            'adPlaced' => ['0', '1', '2'],
            'adPlacedIn' => ['string'],
            'adPlacedDate' => [
                $now->format($format),
                $yesterday->format($format)
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getInvalidFieldValues()
    {
        return [
            'taIsOverridden' => [0],
            'licence' => [0],
            'address' => [
                [
                    'id' => null,
                    'version' => null,
                    'addressLine1' => '',
                    'addressLine2' => null,
                    'addressLine3' => null,
                    'addressLine4' => null,
                    'town' => 'test',
                    'postcode' => null,
                    'countryCode' => 'GB',
                ]
            ],
            'noOfVehiclesRequired' => ['-1', '1000001'],
            'noOfTrailersRequired' => ['-1', '1000001'],
            'permission' => ['T'],
            'adPlaced' => [3]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterTransformations()
    {
        return [
            'taIsOverridden' => [['Y ', 'Y']],
            'licence' => [[99, '99'], ['string', '']],
            'permission' => [[99, '99'], [' Y', 'Y']]
        ];
    }
}
