<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Form\PublicHoliday;
use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicHolidayDateTest extends TestCase
{
    #[DataProvider('invalidDates')]
    public function testInvalidDatesPreserveParts(array $parts, string $messageKey, string $message): void
    {
        $form = $this->createDateForm();
        $form->setData(['fields' => ['holidayDate' => $parts]]);

        self::assertFalse($form->isValid());
        self::assertSame([$messageKey => $message], $form->getMessages()['fields']['holidayDate']);
        $date = $form->get('fields')->get('holidayDate');
        self::assertSame($parts['day'], $date->getDayElement()->getValue());
        self::assertSame($parts['month'], $date->getMonthElement()->getValue());
        self::assertSame($parts['year'], $date->getYearElement()->getValue());
    }

    public static function invalidDates(): array
    {
        return [
            'blank' => [['day' => '', 'month' => '', 'year' => ''], 'dateMissing', 'Enter {{fieldLabel}}'],
            'partial' => [['day' => '12', 'month' => '', 'year' => '2030'], 'dateIncomplete', '{{fieldLabel}} must include a month'],
            'invalid' => [['day' => '31', 'month' => '02', 'year' => '2030'], 'dateInvalid', '{{fieldLabel}} must be a real date'],
        ];
    }

    public function testValidDateIsAccepted(): void
    {
        $form = $this->createDateForm();
        $form->setData(['fields' => ['holidayDate' => ['day' => '12', 'month' => '05', 'year' => '2030']]]);

        self::assertTrue($form->isValid());
        self::assertSame('2030-05-12', $form->getData()['fields']['holidayDate']);
    }

    private function createDateForm(): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(PublicHoliday::class));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'fields'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => $element['spec']['name'] === 'holidayDate'
        ));
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'fields' => [
                'holidayDate' => $spec['input_filter']['fields']['holidayDate'],
                'type' => $spec['input_filter']['fields']['type'],
            ],
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
