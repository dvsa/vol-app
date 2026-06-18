<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\CreateOperatingCentre;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;
use Laminas\Stdlib\ArraySerializableInterface;

class CreateOperatingCentreTest extends TestCase
{
    use CommandTest;


    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new CreateOperatingCentre();
    }

    /**
     * Should return a list of optional fields
     *
     * for example:
     *
     * return ['optionalField', 'anotherOptionalField']
     *
     * Each field is expected to be set to null after validation
     *
     * @return string[]
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
     * Should return an array of valid field values (i.e. those which should pass validation)
     *
     * for example:
     *
     * return [
     *     'fieldName' => [
     *         'good-value-1',
     *         'good-value-2',
     *     ],
     *     'anotherFieldName' => ['good-value'],
     * ];
     *
     * @return array
     */
    protected function getValidFieldValues()
    {
        $now = new \DateTime();
        $yesterday = new \DateTime("yesterday");
        $format = 'Y-m-d';

        return [
            'taIsOverridden' => ['Y', 'N'],
            'application' => ['1', '2'],
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
     * Should return an array of invalid field values (i.e. those which should fail validation)
     *
     * for example:
     *
     * return [
     *     'fieldName' => [
     *         'bad-value-1',
     *         'bad-value-2',
     *     ],
     *     'anotherFieldName' => ['bad-value'],
     * ];
     *
     * @return array
     */
    protected function getInvalidFieldValues()
    {
        return [
            'taIsOverridden' => [0],
            "application" => [0],
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
     * Should return an array of expected transformations which input filters should apply to fields
     *
     * for example:
     *
     * return [
     *     'fieldWhichGetsTrimmed' => [[' string ', 'string']],
     *     'fieldWhichFiltersOutNonNumericDigits => [
     *         ['a1b2c3', '123'],
     *         [99, '99'],
     *     ],
     * ];
     *
     * Tests expect the function 'getFoo' to exist for the function 'foo'.
     *
     * This DOES NOT assert that the value gets validated. To do that @see DtoTest::getValidFieldValues
     *
     * @return array
     */
    protected function getFilterTransformations()
    {
        return [
            'taIsOverridden' => [['Y ', 'Y']],
            'application' => [[99, '99'], ['string', '']],
            'permission' => [[99, '99'], [' Y', 'Y']]
        ];
    }
}
