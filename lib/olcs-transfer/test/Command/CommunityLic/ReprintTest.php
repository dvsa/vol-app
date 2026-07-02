<?php

namespace Dvsa\OlcsTest\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Command\CommunityLic\Reprint;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;
use Laminas\Stdlib\ArraySerializableInterface;

class ReprintTest extends TestCase
{
    use CommandTest;

    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new Reprint();
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
            'user',
            'application'
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
        return [
            'licence' => [
                '1'
            ],
            'application' => [
                '1'
            ],
            'user' => [
                '1'
            ],
            'communityLicenceIds' => [
                ['1']
            ],
            'isBatchReprint' => [
                true,
                false
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
            'communityLicenceIds' => [
                [0]
            ],
            'licence' => [
                0
            ],
            'application' => [
                0
            ],
            'user' => [
                0
            ],
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
            'isBatchReprint' => [
                [2, true]
            ],
            'user' => [
                [0,'0']
            ],
            'application' => [
                [1,'1']
            ]
        ];
    }
}
