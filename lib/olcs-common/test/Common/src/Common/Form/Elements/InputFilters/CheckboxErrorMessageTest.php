<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesConfirmation;
use Common\Form\Model\Form\Lva\Fieldset\FinancialHistoryConfirmation;
use Common\Form\Model\Form\Lva\Fieldset\OperatingCentrePermission;
use Common\Form\Model\Form\Lva\Fieldset\SafetyApplication;
use Common\Form\Model\Form\Lva\Fieldset\TransportManager\Declarations;
use Common\Form\Model\Form\Lva\Fieldset\TransportManager\EmploymentDetails;
use Common\Form\Model\Form\Lva\Fieldset\VariationDeclarationsAndUndertakings;
use Common\Form\Model\Form\Lva\VehiclesDeclarationsMainUndertakings;
use Common\Form\Model\Form\Lva\VehiclesDeclarationsPsvOperateLarge;
use Common\Form\Model\Form\Lva\VehiclesDeclarationsSmallConditions;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormErrors;
use Laminas\Form\Annotation\Options;
use Laminas\Form\Form;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\NotEmpty;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class CheckboxErrorMessageTest extends TestCase
{
    #[DataProvider('checkboxes')]
    public function testUncheckedAndMissingValuesUseTranslatedErrorInBothLocations(
        string $class,
        string $property,
        string $key
    ): void {
        $attribute = (new ReflectionProperty($class, $property))->getAttributes(Options::class)[0];
        $options = $attribute->newInstance()->getOptions();
        self::assertSame($key, $options['error-message']);

        foreach (['N', null] as $value) {
            $checkbox = new SingleCheckbox('checkbox', $options);
            $form = new Form();
            $form->add($checkbox);
            $form->setData($value === null ? [] : ['checkbox' => $value]);
            self::assertFalse($form->isValid());

            $translator = $this->createStub(TranslatorInterface::class);
            $translator->method('translate')->willReturnCallback(
                static fn(string $message): string => $message === $key ? 'Translated checkbox error' : $message
            );
            $formatter = new FormElementMessageFormatter($translator);
            $inline = new FormElementErrors($formatter, $translator);
            $summary = new FormErrors($formatter, $translator);
            self::assertStringContainsString('Translated checkbox error', $inline->render($checkbox));
            self::assertStringContainsString('Translated checkbox error', $summary->render($form));
        }

        $form = new Form();
        $form->add(new SingleCheckbox('checkbox', $options));
        $form->setData(['checkbox' => 'Y']);
        self::assertTrue($form->isValid());
    }

    public static function checkboxes(): array
    {
        return [
            [ConvictionsPenaltiesConfirmation::class, 'convictionsConfirmation', 'checkbox.error.convictions-confirmation'],
            [FinancialHistoryConfirmation::class, 'insolvencyConfirmation', 'checkbox.error.insolvency-confirmation'],
            [OperatingCentrePermission::class, 'permission', 'checkbox.error.operating-centre-permission'],
            [SafetyApplication::class, 'safetyConfirmation', 'checkbox.error.safety-confirmation'],
            [EmploymentDetails::class, 'understoodAvailabilityAgreementConfirmation', 'checkbox.error.tm-availability'],
            [Declarations::class, 'confirmation', 'checkbox.error.tm-declaration'],
            [VariationDeclarationsAndUndertakings::class, 'declarationConfirmation', 'checkbox.error.variation-declaration'],
            [VehiclesDeclarationsMainUndertakings::class, 'psvOccupationRecordsConfirmation', 'checkbox.error.psv-occupation-records'],
            [VehiclesDeclarationsMainUndertakings::class, 'psvIncomeRecordsConfirmation', 'checkbox.error.psv-income-records'],
            [VehiclesDeclarationsPsvOperateLarge::class, 'psvNoSmallVhlConfirmation', 'checkbox.error.psv-large-vehicles'],
            [VehiclesDeclarationsSmallConditions::class, 'psvSmallVhlConfirmation', 'checkbox.error.psv-small-vehicles'],
        ];
    }

    #[DataProvider('uncheckedValues')]
    public function testExistingNotCheckedMessageIsUsedWhenUncheckedOrMissing(?string $value): void
    {
        $checkbox = new SingleCheckbox('checkbox', [
            'must_be_value' => '1',
            'not_checked_message' => 'existing.checkbox.error',
        ]);
        $form = new Form();
        $form->add($checkbox);
        $form->setData($value === null ? [] : ['checkbox' => $value]);
        self::assertFalse($form->isValid());

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('translate')->willReturnCallback(
            static fn(string $message): string => $message === 'existing.checkbox.error' ? 'Existing translated error' : $message
        );
        $formatter = new FormElementMessageFormatter($translator);
        self::assertStringContainsString('Existing translated error', (new FormElementErrors($formatter, $translator))->render($checkbox));
        self::assertStringContainsString('Existing translated error', (new FormErrors($formatter, $translator))->render($form));
    }

    public static function uncheckedValues(): array
    {
        return [['0'], [null]];
    }

    public function testExistingSpecificEmptyMessageIsNotReplaced(): void
    {
        $checkbox = new SingleCheckbox('checkbox', ['not_checked_message' => 'checkbox.confirmation']);
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('translate')->willReturnCallback(static fn(string $message): string => $message);
        $formatter = new FormElementMessageFormatter($translator);

        self::assertSame(
            'Specific empty message',
            $formatter->formatElementMessage($checkbox, 'Specific empty message', NotEmpty::IS_EMPTY)
        );
    }
}
