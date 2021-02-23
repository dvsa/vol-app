<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class OCRSOptInTest
 *
 * @group FormTests
 */
class OCRSOptInTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Vehicle\OCRSOptIn::class;


    /**
     * @test
     * @dataProvider checkboxValueValid_DataProvider
     * @param $value
     */
    public function checkboxValueValid($value)
    {
        $element = ['ocrsCheckbox'];

        $this->assertFormElementValid(
            $element,
            $value
        );
    }

    /**
     * @test
     * @dataProvider checkboxValueNotValid_DataProvider
     * @param $value
     * @param array $expectedValidationErrors
     */
    public function checkboxValueNotValid($value, array $expectedValidationErrors)
    {
        $element = ['ocrsCheckbox'];

        $this->assertFormElementNotValid(
            $element,
            $value,
            $expectedValidationErrors
        );
    }

    /**
     * @dataProvider
     */
    public function checkboxValueValid_DataProvider(): array
    {
        return [
            'Uppercase Y' => ['Y'],
            'Uppercase N' => ['N'],
        ];
    }

    /**
     * @dataProvider
     */
    public function checkboxValueNotValid_DataProvider(): array
    {
        return [
            'Lowercase y' => [
                'y',
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'Startswith capital Y' => [
                'YES',
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'Lowercase n' => [
                'n',
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'Startswith capital N' => [
                'NO',
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'Number 1' => [
                '1',
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'Number 0' => [
                '0',
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'False' => [
                false,
                [
                    \Laminas\Validator\NotEmpty::IS_EMPTY
                ],
            ],
            'True' => [
                true,
                [
                    \Laminas\Validator\InArray::NOT_IN_ARRAY
                ],
            ],
            'Whitespace' => [
                ' ',
                [
                    \Laminas\Validator\NotEmpty::IS_EMPTY
                ],
            ],
            'Empty string' => [
                '',
                [
                    \Laminas\Validator\NotEmpty::IS_EMPTY
                ],
            ],
            'Null' => [
                null,
                [
                    \Laminas\Validator\NotEmpty::IS_EMPTY
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function submitIsActionButton()
    {
        $element = ['submit'];
        $this->assertFormElementActionButton($element);
    }
}
