<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Messaging\Conversations;

use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByOrganisation;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;
use Dvsa\OlcsTest\Transfer\Query\QueryTest;
use Laminas\Stdlib\ArraySerializableInterface;

class ByOrganisationTest extends \PHPUnit\Framework\TestCase
{
    use QueryTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof QueryTest;
    }

    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new ByOrganisation();
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
            'organisation' => [
                '1',
            ],
            'page' => [
                '1',
            ],
            'limit' => [
                '1',
            ],
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
        $invalidNumbers = [0,
            'a',
            '-',
            '\n',];

        return [
            'organisation' => $invalidNumbers,
            'page' => $invalidNumbers,
            'limit' => $invalidNumbers,
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
            'organisation' => [[99, '99']],
            'page' => [[99, '99']],
            'limit' => [[99, '99']],
        ];
    }
}
