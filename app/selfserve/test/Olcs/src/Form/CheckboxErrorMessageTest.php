<?php

declare(strict_types=1);

namespace OlcsTest\Form;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormErrors;
use Laminas\Form\Annotation\Options;
use Laminas\Form\Form;
use Laminas\I18n\Translator\TranslatorInterface;
use Olcs\Form\Model\Fieldset\AgreeTerms;
use Olcs\Form\Model\Fieldset\OperatorRegistration;
use Olcs\Form\Model\Fieldset\RegisterConsultantAccount;
use Olcs\Form\Model\Fieldset\UserRegistration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class CheckboxErrorMessageTest extends TestCase
{
    #[DataProvider('termsForms')]
    public function testUncheckedTermsUseTranslatedError(string $class): void
    {
        $options = (new ReflectionProperty($class, 'termsAgreed'))->getAttributes(Options::class)[0]
            ->newInstance()->getOptions();
        self::assertSame('checkbox.error.terms', $options['error-message']);
        foreach (['N', null] as $value) {
            $checkbox = new SingleCheckbox('terms', $options);
            $form = new Form();
            $form->add($checkbox);
            $form->setData($value === null ? [] : ['terms' => $value]);
            self::assertFalse($form->isValid());
            $translator = $this->createStub(TranslatorInterface::class);
            $translator->method('translate')->willReturnCallback(
                static fn(string $message): string => $message === 'checkbox.error.terms' ? 'Translated terms error' : $message
            );
            $formatter = new FormElementMessageFormatter($translator);
            self::assertStringContainsString('Translated terms error', (new FormElementErrors($formatter, $translator))->render($checkbox));
            self::assertStringContainsString('Translated terms error', (new FormErrors($formatter, $translator))->render($form));
        }

        $form = new Form();
        $form->add(new SingleCheckbox('terms', $options));
        $form->setData(['terms' => 'Y']);
        self::assertTrue($form->isValid());
    }

    public static function termsForms(): array
    {
        return [
            [AgreeTerms::class],
            [OperatorRegistration::class],
            [RegisterConsultantAccount::class],
            [UserRegistration::class],
        ];
    }
}
