<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Form\SystemInfoMessage;
use Common\Filter\DateTimeSelectNullifier;
use Common\Form\Elements\Custom\DateTimeSelect;
use Common\Validator\DateCompare;
use Common\Validator\DateInFuture;
use Common\Validator\ValidateIf;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SystemInfoMessageDateTest extends TestCase
{
    #[DataProvider('dateFields')]
    public function testPartialDateIsRejectedAndValuesArePreserved(string $field): void
    {
        $form = $this->createDateForm();
        $date = ['day' => '12', 'month' => '', 'year' => '2030', 'hour' => '12', 'minute' => '00'];
        $valid = ['day' => '13', 'month' => '01', 'year' => '2030', 'hour' => '12', 'minute' => '00'];
        $form->setData(['details' => ['startDate' => $valid, 'endDate' => $valid, $field => $date]]);

        self::assertFalse($form->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a month'],
            $form->getMessages()['details'][$field]
        );
        $element = $form->get('details')->get($field);
        self::assertSame('12', $element->getDayElement()->getValue());
        self::assertSame('', $element->getMonthElement()->getValue());
        self::assertSame('2030', $element->getYearElement()->getValue());
        self::assertSame('12', $element->getHourElement()->getValue());
        self::assertSame('00', $element->getMinuteElement()->getValue());
    }

    public static function dateFields(): array
    {
        return ['start' => ['startDate'], 'end' => ['endDate']];
    }

    public function testMissingDateWithTimeIsRejected(): void
    {
        $input = new \Common\InputFilter\DateTimeParts('startDate');
        $input->setRequired(false);
        $input->setValue(['day' => '', 'month' => '', 'year' => '', 'hour' => '00', 'minute' => '00']);

        self::assertFalse($input->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a day, month and year'],
            $input->getMessages()
        );
    }

    public function testCompleteDatesStillUseExistingComparison(): void
    {
        $form = $this->createDateForm();
        $start = ['day' => '12', 'month' => '01', 'year' => '2030', 'hour' => '12', 'minute' => '00'];
        $end = ['day' => '13', 'month' => '01', 'year' => '2030', 'hour' => '12', 'minute' => '00'];
        $form->setData(['details' => ['startDate' => $start, 'endDate' => $end]]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('2030-01-12 12:00:00', $form->getData()['details']['startDate']);

        $form = $this->createDateForm();
        $form->setData(['details' => ['startDate' => $end, 'endDate' => $start]]);
        self::assertFalse($form->isValid());
        self::assertNotEmpty($form->getMessages()['details']['endDate']);
    }

    public function testBlankOptionalDateTimeRemainsOptional(): void
    {
        $input = new \Common\InputFilter\DateTimeParts('startDate');
        $input->setRequired(false);
        $input->setValue(['day' => '', 'month' => '', 'year' => '', 'hour' => '', 'minute' => '']);

        self::assertTrue($input->isValid());
    }

    private function createDateForm(): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(SystemInfoMessage::class));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'details'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => in_array($element['spec']['name'], ['startDate', 'endDate'], true)
        ));
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'details' => [
                'startDate' => $spec['input_filter']['details']['startDate'],
                'endDate' => $spec['input_filter']['details']['endDate'],
                'type' => $spec['input_filter']['details']['type'],
            ],
        ];

        $elements = new FormElementManager(new ServiceManager());
        $elements->setAlias('DateTimeSelect', DateTimeSelect::class);
        $elements->setInvokableClass(DateTimeSelect::class, DateTimeSelect::class);
        $factory = new Factory($elements);
        $filters = new FilterPluginManager(new ServiceManager());
        $filters->setAlias('DateTimeSelectNullifier', DateTimeSelectNullifier::class);
        $filters->setInvokableClass(DateTimeSelectNullifier::class, DateTimeSelectNullifier::class);
        $factory->getInputFilterFactory()->getDefaultFilterChain()->setPluginManager($filters);
        $validators = new ValidatorPluginManager(new ServiceManager());
        $validators->setAlias('ValidateIf', ValidateIf::class);
        $validators->setInvokableClass(ValidateIf::class, ValidateIf::class);
        $validators->setAlias('DateCompare', DateCompare::class);
        $validators->setInvokableClass(DateCompare::class, DateCompare::class);
        $validators->setAlias('DateInFuture', DateInFuture::class);
        $validators->setInvokableClass(DateInFuture::class, DateInFuture::class);
        $factory->getInputFilterFactory()->getDefaultValidatorChain()->setPluginManager($validators);

        return $factory->createForm($spec);
    }
}
