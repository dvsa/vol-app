<?php

namespace Dvsa\OlcsTest\Transfer;

use Dvsa\Olcs\Transfer\Command\CommandContainer;
use Dvsa\Olcs\Transfer\Query\QueryContainer;
use Dvsa\OlcsTest\Transfer\Query\QueryTest;
use PHPUnit\Framework\Assert as Assert;
use ReflectionMethod;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Trait DtoTest
 *
 * Do not use this directly - @see QueryTest
 */
trait DtoTest
{
    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    abstract protected function createBlankDto();

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
    abstract protected function getOptionalDtoFields();

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
    abstract protected function getValidFieldValues();

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
    abstract protected function getInvalidFieldValues();

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
    abstract protected function getFilterTransformations();

    /**
     * @return QueryContainer|CommandContainer
     */
    abstract protected function createDtoContainer(ArraySerializableInterface $dto);

    /**
     * @dataProvider provideValidFieldsValidateCases
     *
     * @param string $fieldName
     */
    public function testValidFieldsValidate(array $fieldValues, $fieldName)
    {
        $dto = $this->createPopulatedDto($fieldValues);
        $this->assertDtoFieldValid($dto, $fieldName);
    }

    /**
     * @dataProvider provideValidFieldsRemainUnchangedCases
     *
     * @param $fieldName
     * @param $fieldValue
     */
    public function testValidFieldsRemainUnchanged($fieldName, $fieldValue)
    {
        $dto = $this->createPopulatedDto([$fieldName => $fieldValue]);
        $dtoContainer = $this->createDtoContainer($dto);
        $dtoContainer->getInputFilter()->setValidationGroup([$fieldName]);
        $dtoContainer->isValid();

        $actual = $dto->getArrayCopy()[$fieldName];
        Assert::assertSame(
            $fieldValue,
            $actual,
            sprintf(
                "Expected %s value %s to remain the same, but it got transformed to %s",
                $fieldName,
                var_export($fieldValue, true),
                var_export($actual, true)
            )
        );
    }

    /**
     * @dataProvider provideInvalidFieldCases
     *
     * @param $fieldName
     * @param $value
     */
    public function testInvalidField($fieldName, $value)
    {
        $fieldValues = [$fieldName => $value];
        $dto = $this->createPopulatedDto($fieldValues);
        $this->assertDtoFieldInvalid($dto, $fieldName);
    }

    /**
     * @dataProvider provideFieldTransformationCases
     *
     * @param string $fieldName
     */
    public function testFieldTransformations($fieldName, mixed $inputValue, mixed $expectedValue)
    {
        $fieldValues = [$fieldName => $inputValue];
        $dto = $this->createPopulatedDto($fieldValues);
        $this->assertTransformation($dto, $fieldName, $inputValue, $expectedValue);
    }

    /**
     * @dataProvider provideGetterCases
     *
     * @param $fieldName
     */
    public function testGetterNames($fieldName)
    {
        Assert::assertSame(
            "get" . ucwords($fieldName),
            (new ReflectionMethod($this->createBlankDto(), "get$fieldName"))->getName(),
            "Getter for $fieldName is named incorrectly name"
        );
    }

    /**
     * @dataProvider provideGetterCases
     *
     * @param $fieldName
     */
    public function testGetters($fieldName)
    {
        $dto = $this->createPopulatedDto([$fieldName => 'DUMMY-VALUE']);

        Assert::assertTrue(
            method_exists($dto, "get$fieldName"),
            "Getter for $fieldName doesn't exit"
        );

        Assert::assertSame(
            'DUMMY-VALUE',
            $dto->{"get$fieldName"}(),
            "Getter for $fieldName did not return the correct data"
        );
    }

    /**
     * @dataProvider provideDefaultValueCases
     *
     * @param $fieldName
     */
    public function testDefaultValues($fieldName)
    {
        $dto = $this->createBlankDto();
        $dtoContainer = $this->createDtoContainer($dto);
        $inputFilter = $dtoContainer->getInputFilter();
        $inputFilter->setValidationGroup([$fieldName]);
        $dtoContainer->isValid();

        Assert::assertNull(
            $inputFilter->getValues()[$fieldName],
            sprintf("Expected %s to be when omitted", $fieldName)
        );
    }

    public function provideDefaultValueCases()
    {
        foreach ($this->getOptionalDtoFields() as $fieldName) {
            yield [$fieldName];
        }
    }

    public function provideGetterCases()
    {
        $fieldNames = array_unique(
            array_merge(
                $this->getOptionalDtoFields(),
                array_keys($this->getValidFieldValues()),
                array_keys($this->getInvalidFieldValues()),
                array_keys($this->getFilterTransformations())
            )
        );
        foreach ($fieldNames as $fieldName) {
            yield [$fieldName];
        }
    }

    public function provideValidFieldsValidateCases()
    {
        foreach ($this->getOptionalDtoFields() as $field) {
            yield "$field optional - null" => [[$field => null], $field];
            yield "$field optional - ''" => [[$field => ''], $field];
            yield "$field optional - false" => [[$field => false], $field];
            yield "$field optional - []" => [[$field => []], $field];
            yield "$field optional - unset" => [[], $field];
        }

        foreach ($this->getValidFieldValues() as $fieldName => $validValues) {
            foreach ($validValues as $validValue) {
                yield "$fieldName valid - " . var_export($validValue, true) => [
                    [$fieldName => $validValue],
                    $fieldName
                ];
            }
        }
    }

    public function provideValidFieldsRemainUnchangedCases()
    {
        foreach ($this->getValidFieldValues() as $fieldName => $validValues) {
            foreach ($validValues as $validValue) {
                yield [$fieldName, $validValue];
            }
        }
    }

    public function provideInvalidFieldCases()
    {
        $invalidFieldValues = $this->getInvalidFieldValues();
        foreach ($invalidFieldValues as $fieldName => $invalidValues) {
            foreach ($invalidValues as $invalidValue) {
                yield "$fieldName invalid - " . var_export($invalidValue, true) => [$fieldName, $invalidValue];
            }
        }
    }

    public function provideFieldTransformationCases()
    {
        foreach ($this->getFilterTransformations() as $fieldName => $transformations) {
            foreach ($transformations as [$inputValue, $expectedValue]) {
                yield [$fieldName, $inputValue, $expectedValue];
            }
        }
    }


    protected function assertDtoFieldValid(ArraySerializableInterface $dto, $fieldName)
    {
        $dtoContainer = $this->createDtoContainer($dto);
        $dtoContainer->getInputFilter()->setValidationGroup([$fieldName]);
        Assert::assertTrue(
            $dtoContainer->isValid(),
            sprintf(
                "Data should be valid, but failed with these messages: %s",
                json_encode($dtoContainer->getMessages())
            )
        );
    }

    protected function assertDtoFieldInvalid(ArraySerializableInterface $dto, $fieldName)
    {
        $dtoContainer = $this->createDtoContainer($dto);
        $inputFilter = $dtoContainer->getInputFilter();
        $inputFilter->setValidationGroup([$fieldName]);

        Assert::assertFalse(
            $dtoContainer->isValid(),
            "Expected $fieldName to be invalid \n" . print_r($dto, true)
        );

        $actualInvalidFieldNames = array_keys($inputFilter->getInvalidInput());

        Assert::assertSame(
            [$fieldName],
            $actualInvalidFieldNames,
            sprintf(
                "Expected single validation failure on $fieldName, instead recieved the following violations: %sa\n %s",
                json_encode($actualInvalidFieldNames),
                print_r($dto, true)
            )
        );
    }

    protected function assertTransformation($dto, $fieldName, $inputValue, $expectedValue)
    {
        $dtoContainer = $this->createDtoContainer($dto);
        $inputFilter = $dtoContainer->getInputFilter();
        $inputFilter->setValidationGroup([$fieldName]);
        $dtoContainer->isValid();

        $actual = $inputFilter->getValues()[$fieldName];

        Assert::assertSame(
            $expectedValue,
            $actual,
            sprintf(
                "Expected %s to be transformed from %s to %s",
                $fieldName,
                var_export($inputValue, true),
                var_export($expectedValue, true)
            )
        );
    }

    protected function createPopulatedDto($fieldValues)
    {
        $dto = $this->createBlankDto();
        $dto->exchangeArray($fieldValues);
        return $dto;
    }
}
