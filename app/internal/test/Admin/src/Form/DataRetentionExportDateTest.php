<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Form\DataRetentionExport;
use Common\Form\Elements\Custom\DateSelect;
use Common\InputFilter\DateParts;
use Common\Validator\DateCompare;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataRetentionExportDateTest extends TestCase
{
    #[DataProvider('dateFields')]
    public function testPartialDateIsRejectedAndPreserved(string $field): void
    {
        $form = $this->createDateForm();
        $start = ['day' => '01', 'month' => '01', 'year' => '2020'];
        $end = ['day' => '31', 'month' => '12', 'year' => '2020'];
        $partial = ['day' => '12', 'month' => '', 'year' => '2020'];
        $form->setData(['exportOptions' => ['startDate' => $start, 'endDate' => $end, $field => $partial]]);

        self::assertInstanceOf(DateParts::class, $form->getInputFilter()->get('exportOptions')->get($field));
        self::assertFalse($form->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a month'],
            $form->getMessages()['exportOptions'][$field]
        );
        $date = $form->get('exportOptions')->get($field);
        self::assertSame('12', $date->getDayElement()->getValue());
        self::assertSame('', $date->getMonthElement()->getValue());
        self::assertSame('2020', $date->getYearElement()->getValue());
    }

    public static function dateFields(): array
    {
        return ['start' => ['startDate'], 'end' => ['endDate']];
    }

    public function testRequiredBlankDateAndInvalidDateAreRejected(): void
    {
        $form = $this->createDateForm();
        $form->setData(['exportOptions' => [
            'startDate' => ['day' => '', 'month' => '', 'year' => ''],
            'endDate' => ['day' => '31', 'month' => '02', 'year' => '2020'],
        ]]);
        self::assertFalse($form->isValid());
        self::assertArrayHasKey('dateMissing', $form->getMessages()['exportOptions']['startDate']);
        self::assertArrayHasKey('dateInvalid', $form->getMessages()['exportOptions']['endDate']);
    }

    public function testCompleteDateRangeAndExistingComparison(): void
    {
        $start = ['day' => '01', 'month' => '01', 'year' => '2020'];
        $end = ['day' => '31', 'month' => '12', 'year' => '2020'];
        $form = $this->createDateForm();
        $form->setData(['exportOptions' => ['startDate' => $start, 'endDate' => $end]]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('2020-01-01', $form->getData()['exportOptions']['startDate']);
        self::assertSame('2020-12-31', $form->getData()['exportOptions']['endDate']);

        $form = $this->createDateForm();
        $form->setData(['exportOptions' => ['startDate' => $end, 'endDate' => $start]]);
        self::assertFalse($form->isValid());
        self::assertNotEmpty($form->getMessages()['exportOptions']['endDate']);
    }

    private function createDateForm(): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(DataRetentionExport::class));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'exportOptions'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => in_array($element['spec']['name'], ['startDate', 'endDate'], true)
        ));
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'exportOptions' => [
                'startDate' => $spec['input_filter']['exportOptions']['startDate'],
                'endDate' => $spec['input_filter']['exportOptions']['endDate'],
                'type' => $spec['input_filter']['exportOptions']['type'],
            ],
        ];

        $elements = new FormElementManager(new ServiceManager());
        $elements->setAlias('DateSelect', DateSelect::class);
        $elements->setInvokableClass(DateSelect::class, DateSelect::class);
        $factory = new Factory($elements);
        $validators = new ValidatorPluginManager(new ServiceManager());
        $validators->setAlias('DateCompare', DateCompare::class);
        $validators->setInvokableClass(DateCompare::class, DateCompare::class);
        $factory->getInputFilterFactory()->getDefaultValidatorChain()->setPluginManager($validators);

        return $factory->createForm($spec);
    }
}
