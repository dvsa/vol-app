<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Form\CpmsReport;
use Admin\Form\Model\Form\PermitsReport;
use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Common\Validator\DateCompare;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportDateTest extends TestCase
{
    #[DataProvider('dateFields')]
    public function testPartialDateIsRejectedAndRetained(string $formClass, string $field): void
    {
        $form = $this->createDateForm($formClass);
        $start = ['day' => '01', 'month' => '01', 'year' => '2020'];
        $end = ['day' => '31', 'month' => '12', 'year' => '2020'];
        $partial = ['day' => '12', 'month' => '', 'year' => '2020'];
        $form->setData(['reportOptions' => ['startDate' => $start, 'endDate' => $end, $field => $partial]]);

        self::assertFalse($form->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a month'],
            $form->getMessages()['reportOptions'][$field]
        );
        $date = $form->get('reportOptions')->get($field);
        self::assertSame('12', $date->getDayElement()->getValue());
        self::assertSame('', $date->getMonthElement()->getValue());
        self::assertSame('2020', $date->getYearElement()->getValue());
    }

    public static function dateFields(): array
    {
        return [
            'permits start' => [PermitsReport::class, 'startDate'],
            'permits end' => [PermitsReport::class, 'endDate'],
            'cpms start' => [CpmsReport::class, 'startDate'],
            'cpms end' => [CpmsReport::class, 'endDate'],
        ];
    }

    #[DataProvider('reportForms')]
    public function testExistingDateRulesRemainInEffect(string $formClass): void
    {
        $form = $this->createDateForm($formClass);
        $start = ['day' => '01', 'month' => '01', 'year' => '2020'];
        $end = ['day' => '31', 'month' => '12', 'year' => '2020'];
        $form->setData(['reportOptions' => ['startDate' => $start, 'endDate' => $end]]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('2020-01-01', $form->getData()['reportOptions']['startDate']);

        $form = $this->createDateForm($formClass);
        $form->setData(['reportOptions' => ['startDate' => $end, 'endDate' => $start]]);
        self::assertFalse($form->isValid());
        self::assertNotEmpty($form->getMessages()['reportOptions']['endDate']);

        $form = $this->createDateForm($formClass);
        $form->setData(['reportOptions' => [
            'startDate' => $start,
            'endDate' => ['day' => '01', 'month' => '01', 'year' => '9999'],
        ]]);
        self::assertFalse($form->isValid());
        self::assertArrayHasKey('inFuture', $form->getMessages()['reportOptions']['endDate']);
    }

    public static function reportForms(): array
    {
        return ['permits' => [PermitsReport::class], 'cpms' => [CpmsReport::class]];
    }

    private function createDateForm(string $formClass): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification($formClass));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'reportOptions'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => in_array($element['spec']['name'], ['startDate', 'endDate'], true)
        ));
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'reportOptions' => [
                'startDate' => $spec['input_filter']['reportOptions']['startDate'],
                'endDate' => $spec['input_filter']['reportOptions']['endDate'],
                'type' => $spec['input_filter']['reportOptions']['type'],
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
        $validators = new ValidatorPluginManager(new ServiceManager());
        $validators->setAlias('DateCompare', DateCompare::class);
        $validators->setInvokableClass(DateCompare::class, DateCompare::class);
        $factory->getInputFilterFactory()->getDefaultValidatorChain()->setPluginManager($validators);

        return $factory->createForm($spec);
    }
}
