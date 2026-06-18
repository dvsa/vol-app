<?php

namespace Dvsa\OlcsTest\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\Query\Fee\InterimRefunds;
use Dvsa\OlcsTest\Transfer\DtoWithoutFieldTransformationsTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;
use Dvsa\OlcsTest\Transfer\Query\QueryTest;
use PHPUnit\Framework\TestCase;
use Laminas\Stdlib\ArraySerializableInterface;

class InterimRefundsTest extends TestCase
{
    use QueryTest, DtoWithoutFieldTransformationsTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutFieldTransformationsTest::testFieldTransformations insteadof QueryTest;
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof QueryTest;
    }

    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new InterimRefunds();
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
            'order' => [
                'ASC',
                'DESC'
            ],
            'sort' => [
                'field',
                'field1',
                'field_1',
                'field.1',
                'field 1'
            ],
            'startDate' => [
                '2019-06-01',
                '2019-05-04'
            ],
            'endDate' => [
                '2019-06-30',
                '2019-05-30'
            ],
            'trafficAreas' => [
                ['B'],
                ['B', 'C'],
                ['B', 'C', 'F'],
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
            'order' => [
                'BLARG',
                'ARG',
                'FOO',
                ''
            ],
            'sort' => [
                '',
                'field%$£'
            ],
            'startDate' => [
                '2019',
                '05-04',
                'fsafde',
                ''
            ],
            'endDate' => [
                '2019',
                '05-30',
                'abfdew',
                ''
            ],
            'trafficAreas' => [
                ['GTRR', 'GHY']
            ]
        ];
    }
}
