<?php

namespace Dvsa\OlcsTest\Transfer\Query\Surrender;

use Dvsa\Olcs\Transfer\Query\DataRetention\Records;
use Dvsa\OlcsTest\Transfer\Query\QueryTest;
use Laminas\Stdlib\ArraySerializableInterface;
use PHPUnit\Framework\TestCase;

class RecordsTest extends TestCase
{
    use QueryTest;

    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new Records();
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
            'dataRetentionRuleId' => [
                '1'
            ],
            'goodsOrPsv' => [
                'lcat_gv',
                'lcat_psv',
                '',
                null
            ],
            'nextReview' => [
                'pending',
                'deferred',
                '',
                null
            ],
            'markedForDeletion' => [
                'Y',
                'N',
                '',
                null
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
            "dataRetentionRuleId" => [
                0,
            ],
            'goodsOrPsv' => [
                'test',
                '1',
                0
            ],
            'nextReview' => [
                'test',
                '1',
                0
            ],
            'markedForDeletion' => [
                'test',
                '1',
                0
            ]
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
            'dataRetentionRuleId' => [[99, '99']],
            'goodsOrPsv' => [
                ['pending ', 'pending'],
                [' pending', 'pending'],
                [' pending ', 'pending']
            ],
            'markedForDeletion' => [
                ['Y ', 'Y'],
                [' Y', 'Y'],
                [' Y ', 'Y']
            ]
        ];
    }

    protected function getOptionalDtoFields()
    {
        return [
            'nextReview',
            'markedForDeletion',
            'assignedToUser',
            'goodsOrPsv',
            'user'
        ];
    }
}
