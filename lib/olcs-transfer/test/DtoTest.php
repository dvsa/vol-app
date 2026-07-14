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
 *
 * NOTE: cases are iterated inside each test method rather than supplied via
 * @dataProvider. PHPUnit 10+ requires data providers to be static, but these
 * cases are derived from the per-DTO abstract methods below (getValidFieldValues
 * etc.) which are instance state, so a static provider cannot reach them.
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

    public function testValidFieldsValidate(): void
    {
        $asserted = false;

        foreach ($this->getOptionalDtoFields() as $field) {
            foreach ([[$field => null], [$field => ''], [$field => false], [$field => []], []] as $fieldValues) {
                $this->assertDtoFieldValid($this->createPopulatedDto($fieldValues), $field);
                $asserted = true;
            }
        }

        foreach ($this->getValidFieldValues() as $fieldName => $validValues) {
            foreach ($validValues as $validValue) {
                $this->assertDtoFieldValid($this->createPopulatedDto([$fieldName => $validValue]), $fieldName);
                $asserted = true;
            }
        }

        if (!$asserted) {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testValidFieldsRemainUnchanged(): void
    {
        $asserted = false;

        foreach ($this->getValidFieldValues() as $fieldName => $validValues) {
            foreach ($validValues as $fieldValue) {
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
                $asserted = true;
            }
        }

        if (!$asserted) {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testInvalidField(): void
    {
        $asserted = false;

        foreach ($this->getInvalidFieldValues() as $fieldName => $invalidValues) {
            foreach ($invalidValues as $value) {
                $dto = $this->createPopulatedDto([$fieldName => $value]);
                $this->assertDtoFieldInvalid($dto, $fieldName);
                $asserted = true;
            }
        }

        if (!$asserted) {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testFieldTransformations(): void
    {
        $asserted = false;

        foreach ($this->getFilterTransformations() as $fieldName => $transformations) {
            foreach ($transformations as [$inputValue, $expectedValue]) {
                $dto = $this->createPopulatedDto([$fieldName => $inputValue]);
                $this->assertTransformation($dto, $fieldName, $inputValue, $expectedValue);
                $asserted = true;
            }
        }

        if (!$asserted) {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testGetterNames(): void
    {
        $fieldNames = $this->getterCaseFieldNames();

        if ($fieldNames === []) {
            $this->expectNotToPerformAssertions();
            return;
        }

        foreach ($fieldNames as $fieldName) {
            Assert::assertSame(
                "get" . ucwords((string) $fieldName),
                new ReflectionMethod($this->createBlankDto(), "get$fieldName")->getName(),
                "Getter for $fieldName is named incorrectly name"
            );
        }
    }

    public function testGetters(): void
    {
        $fieldNames = $this->getterCaseFieldNames();

        if ($fieldNames === []) {
            $this->expectNotToPerformAssertions();
            return;
        }

        foreach ($fieldNames as $fieldName) {
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
    }

    public function testDefaultValues(): void
    {
        $optionalFields = $this->getOptionalDtoFields();

        if ($optionalFields === []) {
            $this->expectNotToPerformAssertions();
            return;
        }

        foreach ($optionalFields as $fieldName) {
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
    }

    /**
     * Unique set of field names referenced across the optional, valid, invalid and
     * transformation field definitions - used by the getter tests.
     */
    private function getterCaseFieldNames(): array
    {
        return array_unique(
            array_merge(
                $this->getOptionalDtoFields(),
                array_keys($this->getValidFieldValues()),
                array_keys($this->getInvalidFieldValues()),
                array_keys($this->getFilterTransformations())
            )
        );
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
