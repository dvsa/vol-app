<?php

namespace Dvsa\OlcsTest\Transfer\Command\Disqualification;

use Dvsa\Olcs\Transfer\Command\Disqualification\Delete;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutFieldTransformationsTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;
use PHPUnit\Framework\TestCase;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Class DeleteTest
 *
 * @package Dvsa\OlcsTest\Transfer\Command\Disqualification
 */
class DeleteTest extends TestCase
{
    use CommandTest, DtoWithoutFieldTransformationsTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutFieldTransformationsTest::testFieldTransformations insteadof CommandTest;
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof CommandTest;
    }

    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new Delete();
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
            'id' => ['1', '2']
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
            'id' => ['string', ['unexpected' => 'array']]
        ];
    }
}
