<?php

declare(strict_types=1);

namespace CommonTest\InputFilter;

use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Common\Form\Model\Form\Lva\PreviousConviction;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PreviousConvictionDateTest extends TestCase
{
    #[DataProvider('invalidDates')]
    public function testInvalidDatePreservesInput(array $parts, string $key, string $message): void
    {
        $form = $this->createDateForm();
        $form->setData(['data' => ['convictionDate' => $parts]]);

        self::assertFalse($form->isValid());
        self::assertSame([$key => $message], $form->getMessages()['data']['convictionDate']);
        $date = $form->get('data')->get('convictionDate');
        self::assertNull($date->getOption('error-message'));
        self::assertSame($parts['day'], $date->getDayElement()->getValue());
        self::assertSame($parts['month'], $date->getMonthElement()->getValue());
        self::assertSame($parts['year'], $date->getYearElement()->getValue());
    }

    public static function invalidDates(): array
    {
        return [
            'blank' => [['day' => '', 'month' => '', 'year' => ''], 'dateMissing', 'Enter {{fieldLabel}}'],
            'partial' => [['day' => '12', 'month' => '', 'year' => '2020'], 'dateIncomplete', '{{fieldLabel}} must include a month'],
            'invalid' => [['day' => '31', 'month' => '02', 'year' => '2020'], 'dateInvalid', '{{fieldLabel}} must be a real date'],
            'future' => [['day' => '01', 'month' => '01', 'year' => '9999'], 'inFuture', 'This date is not allowed to be in the future'],
        ];
    }

    public function testValidDateRetainsExistingConversion(): void
    {
        $form = $this->createDateForm();
        $form->setData(['data' => ['convictionDate' => ['day' => '12', 'month' => '05', 'year' => '2020']]]);

        self::assertTrue($form->isValid());
        self::assertSame('2020-05-12', $form->getData()['data']['convictionDate']);
    }

    private function createDateForm(): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(PreviousConviction::class));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'data'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => $element['spec']['name'] === 'convictionDate'
        ));
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'data' => [
                'convictionDate' => $spec['input_filter']['data']['convictionDate'],
                'type' => $spec['input_filter']['data']['type'],
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
