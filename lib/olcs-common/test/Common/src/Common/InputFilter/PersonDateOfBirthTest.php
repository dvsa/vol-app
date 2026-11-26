<?php

declare(strict_types=1);

namespace CommonTest\InputFilter;

use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Common\Form\Model\Form\Lva\Person;
use Common\Form\Model\Form\Lva\SoleTrader;
use Common\InputFilter\PersonDateOfBirth;
use Common\InputFilter\OffenceDateOfBirth;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PersonDateOfBirthTest extends TestCase
{
    #[DataProvider('invalidDates')]
    public function testBothPersonFormsValidateRawParts(array $parts, string $message): void
    {
        foreach ([Person::class, SoleTrader::class] as $formClass) {
            $form = $this->createDateForm($formClass);
            $form->setData(['data' => ['birthDate' => $parts]]);

            self::assertFalse($form->isValid(), $formClass);
            self::assertSame([$message], array_values($form->getMessages()['data']['birthDate']));
            $date = $form->get('data')->get('birthDate');
            self::assertSame($parts['day'], $date->getDayElement()->getValue());
            self::assertSame($parts['month'], $date->getMonthElement()->getValue());
            self::assertSame($parts['year'], $date->getYearElement()->getValue());
        }
    }

    public static function invalidDates(): array
    {
        return [
            'blank' => [['day' => '', 'month' => '', 'year' => ''], 'Enter {{fieldLabel}}'],
            'missing month' => [['day' => '12', 'month' => '', 'year' => '1990'], '{{fieldLabel}} must include a month'],
            'missing day and year' => [['day' => '', 'month' => '2', 'year' => ''], '{{fieldLabel}} must include a day and year'],
            'zero day' => [['day' => '0', 'month' => '1', 'year' => '1990'], '{{fieldLabel}} must be a real date'],
            'zero month' => [['day' => '1', 'month' => '0', 'year' => '1990'], '{{fieldLabel}} must be a real date'],
            'letters' => [['day' => 'ab', 'month' => '1', 'year' => '1990'], '{{fieldLabel}} must be a real date'],
            'short year' => [['day' => '1', 'month' => '1', 'year' => '99'], '{{fieldLabel}} must be a real date'],
            'invalid leap day' => [['day' => '29', 'month' => '2', 'year' => '2023'], '{{fieldLabel}} must be a real date'],
        ];
    }

    public function testOptionalBlankDateIsAllowedButPartialDateIsNot(): void
    {
        $input = new PersonDateOfBirth('birthDate');
        $input->setRequired(false);
        $input->getFilterChain()->attach(new DateSelectNullifier());
        $input->setValue(['day' => '', 'month' => '', 'year' => '']);
        self::assertTrue($input->isValid());
        self::assertNull($input->getValue());

        $input->setValue(['day' => '12', 'month' => '', 'year' => '1990']);
        self::assertFalse($input->isValid());
        self::assertSame(['dateIncomplete' => '{{fieldLabel}} must include a month'], $input->getMessages());
    }

    public function testValidDateStillUsesExistingFiltersAndValidators(): void
    {
        foreach ([Person::class, SoleTrader::class] as $formClass) {
            $form = $this->createDateForm($formClass);
            $form->setData(['data' => ['birthDate' => ['day' => '29', 'month' => '02', 'year' => '2000']]]);

            self::assertTrue($form->isValid(), $formClass . ': ' . json_encode($form->getMessages()));
            self::assertSame('2000-02-29', $form->getData()['data']['birthDate']);
        }
    }

    public function testInternalOptionalPersonDateCanBeBlank(): void
    {
        foreach ([Person::class, SoleTrader::class] as $formClass) {
            $form = $this->createDateForm($formClass);
            $input = $form->getInputFilter()->get('data')->get('birthDate');
            $input->setAllowEmpty(true)->setContinueIfEmpty(true);
            $form->setData(['data' => ['birthDate' => ['day' => '', 'month' => '', 'year' => '']]]);

            self::assertTrue($form->isValid(), $formClass . ': ' . json_encode($form->getMessages()));
            self::assertNull($form->getData()['data']['birthDate']);

            $form->setData(['data' => ['birthDate' => ['day' => '1', 'month' => '', 'year' => '2000']]]);
            self::assertFalse($form->isValid(), $formClass);
            self::assertSame(
                ['dateIncomplete' => '{{fieldLabel}} must include a month'],
                $form->getMessages()['data']['birthDate']
            );
        }
    }

    public function testValidSingleDigitPartsAreAccepted(): void
    {
        foreach ([Person::class, SoleTrader::class] as $formClass) {
            $form = $this->createDateForm($formClass);
            $form->setData(['data' => ['birthDate' => ['day' => '1', 'month' => '2', 'year' => '2000']]]);

            self::assertTrue($form->isValid(), $formClass . ': ' . json_encode($form->getMessages()));
        }
    }

    public function testDirectorFutureDateStillUsesExistingValidator(): void
    {
        $form = $this->createDateForm(Person::class);
        $form->setData(['data' => ['birthDate' => ['day' => '01', 'month' => '01', 'year' => '9999']]]);

        self::assertFalse($form->isValid());
        self::assertArrayHasKey('inFuture', $form->getMessages()['data']['birthDate']);
    }

    public function testConvictionDateChecksRespectDefendantType(): void
    {
        $input = new OffenceDateOfBirth('birthDate');
        $input->setRequired(false);
        $input->getFilterChain()->attach(new DateSelectNullifier());
        $input->setValue(['day' => '12', 'month' => '', 'year' => '1990']);

        self::assertTrue($input->isValid(['defendantType' => 'def_t_op']));
        self::assertTrue($input->isValid(['defendantType' => '']));
        self::assertFalse($input->isValid(['defendantType' => 'def_t_dr']));
        self::assertSame(['dateIncomplete' => '{{fieldLabel}} must include a month'], $input->getMessages());
    }

    public function testSearchDateInputsUseRawDateValidation(): void
    {
        $personSearch = new \Common\Form\Elements\Types\PersonSearch();
        $searchDate = $personSearch->get('birthDate');
        self::assertSame(PersonDateOfBirth::class, $searchDate->getInputSpecification()['type']);
    }

    #[DataProvider('otherCommonPersonForms')]
    public function testOtherCommonDobFieldSpecificationsUseDateValidation(string $formClass, string $fieldset): void
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification($formClass));
        $input = $spec['input_filter'][$fieldset]['birthDate'];

        self::assertSame(PersonDateOfBirth::class, $input['type']);
        $instance = (new \Laminas\InputFilter\Factory())->createInput([
            'name' => 'birthDate',
            'type' => $input['type'],
            'required' => $input['required'] ?? true,
        ]);
        $instance->setValue(['day' => '12', 'month' => '', 'year' => '1990']);
        self::assertFalse($instance->isValid());
        self::assertSame(['dateIncomplete' => '{{fieldLabel}} must include a month'], $instance->getMessages());
    }

    public static function otherCommonPersonForms(): array
    {
        return [
            'licence person' => [\Common\Form\Model\Form\Licence\AddPerson::class, 'data'],
            'add transport manager' => [\Common\Form\Model\Form\Lva\AddTransportManagerDetails::class, 'data'],
            'transport manager details' => [\Common\Form\Model\Form\Lva\TransportManagerDetails::class, 'details'],
        ];
    }

    private function createDateForm(string $formClass): \Laminas\Form\FormInterface
    {
        $builder = new AttributeBuilder();
        $spec = ArrayUtils::iteratorToArray($builder->getFormSpecification($formClass));
        $fieldset = &$spec['fieldsets'][0]['spec'];
        $fieldset['elements'] = array_values(array_filter(
            $fieldset['elements'],
            static fn(array $element): bool => $element['spec']['name'] === 'birthDate'
        ));
        $spec['fieldsets'] = [$spec['fieldsets'][0]];
        $spec['input_filter']['data'] = [
            'birthDate' => $spec['input_filter']['data']['birthDate'],
            'type' => $spec['input_filter']['data']['type'],
        ];

        $elements = new FormElementManager(new ServiceManager());
        $elements->setAlias('DateSelect', DateSelect::class);
        $elements->setInvokableClass(DateSelect::class, DateSelect::class);
        $factory = new Factory($elements);
        $filters = new FilterPluginManager(new ServiceManager());
        $filters->setAlias('DateSelectNullifier', DateSelectNullifier::class);
        $filters->setInvokableClass(DateSelectNullifier::class, DateSelectNullifier::class);
        $factory->getInputFilterFactory()->getDefaultFilterChain()->setPluginManager($filters);

        return $factory->createForm($spec);
    }
}
