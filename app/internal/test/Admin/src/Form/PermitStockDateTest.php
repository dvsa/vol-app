<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Form\IrhpPermitStock;
use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Common\RefData;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PermitStockDateTest extends TestCase
{
    #[DataProvider('visibleFields')]
    public function testVisibleDatesRejectPartialValues(int $type, string $field): void
    {
        $form = $this->createDateForm();
        $date = ['day' => '12', 'month' => '', 'year' => '2027'];
        $form->setData(['permitStockDetails' => ['irhpPermitType' => (string) $type, $field => $date]]);

        self::assertFalse($form->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a month'],
            $form->getMessages()['permitStockDetails'][$field]
        );
        $element = $form->get('permitStockDetails')->get($field);
        self::assertSame('12', $element->getDayElement()->getValue());
        self::assertSame('', $element->getMonthElement()->getValue());
        self::assertSame('2027', $element->getYearElement()->getValue());
    }

    public static function visibleFields(): array
    {
        return [
            'annual start' => [RefData::ECMT_PERMIT_TYPE_ID, 'validFrom'],
            'annual end' => [RefData::ECMT_PERMIT_TYPE_ID, 'validTo'],
            'bilateral start' => [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, 'validFrom'],
        ];
    }

    #[DataProvider('hiddenTypes')]
    public function testHiddenPartialDatesRetainExistingBehaviour(int $type): void
    {
        $form = $this->createDateForm();
        $date = ['day' => '12', 'month' => '', 'year' => '2027'];
        $form->setData(['permitStockDetails' => [
            'irhpPermitType' => (string) $type,
            'validFrom' => $date,
            'validTo' => $date,
        ]]);

        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertNull($form->getData()['permitStockDetails']['validFrom']);
        self::assertNull($form->getData()['permitStockDetails']['validTo']);
    }

    public static function hiddenTypes(): array
    {
        return [
            'removal' => [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
            'vehicle roadworthiness' => [RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID],
            'trailer roadworthiness' => [RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID],
        ];
    }

    public function testVisibleDatesRemainOptionalAndValidDatesAreConverted(): void
    {
        $form = $this->createDateForm();
        $form->setData(['permitStockDetails' => [
            'irhpPermitType' => (string) RefData::ECMT_PERMIT_TYPE_ID,
            'validFrom' => ['day' => '', 'month' => '', 'year' => ''],
            'validTo' => ['day' => '12', 'month' => '03', 'year' => '2027'],
        ]]);

        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertNull($form->getData()['permitStockDetails']['validFrom']);
        self::assertSame('2027-03-12', $form->getData()['permitStockDetails']['validTo']);
    }

    private function createDateForm(): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(IrhpPermitStock::class));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'permitStockDetails'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => in_array(
                $element['spec']['name'],
                ['irhpPermitType', 'validFrom', 'validTo'],
                true
            )
        ));
        foreach ($fieldsets[0]['spec']['elements'] as &$element) {
            if ($element['spec']['name'] === 'irhpPermitType') {
                $element['spec']['type'] = 'Hidden';
            }
        }
        unset($element);
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'permitStockDetails' => [
                'irhpPermitType' => ['name' => 'irhpPermitType'],
                'validFrom' => $spec['input_filter']['permitStockDetails']['validFrom'],
                'validTo' => $spec['input_filter']['permitStockDetails']['validTo'],
                'type' => $spec['input_filter']['permitStockDetails']['type'],
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
