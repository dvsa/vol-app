<?php

declare(strict_types=1);

namespace OlcsTest\Form;

use Admin\Form\Model\Fieldset\PublicHoliday;
use Admin\Form\Model\Form\CacheClear;
use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormErrors;
use Laminas\Form\Annotation\Options;
use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\Form;
use Laminas\I18n\Translator\TranslatorInterface;
use Olcs\Form\Model\Fieldset\CreatePermit;
use Olcs\Form\Model\Fieldset\IrhpApplication\Bottom;
use Olcs\Form\Model\Fieldset\IrhpBilateral\Bottom as BilateralBottom;
use Olcs\Form\Model\Form\TransportManagerMerge;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class CheckboxErrorMessageTest extends TestCase
{
    #[DataProvider('checkboxes')]
    public function testMissingCheckboxUsesTranslatedError(
        string $class,
        string $property,
        string $elementClass,
        string $key,
        string $validValue
    ): void {
        $options = (new ReflectionProperty($class, $property))->getAttributes(Options::class)[0]
            ->newInstance()->getOptions();
        self::assertSame($key, $options['error-message']);
        $element = new $elementClass('checkbox', $options);
        $form = new Form();
        $form->add($element);
        $form->setData([]);
        self::assertFalse($form->isValid());

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('translate')->willReturnCallback(
            static fn(string $message): string => $message === $key ? 'Translated checkbox error' : $message
        );
        $formatter = new FormElementMessageFormatter($translator);
        self::assertStringContainsString('Translated checkbox error', (new FormElementErrors($formatter, $translator))->render($element));
        self::assertStringContainsString('Translated checkbox error', (new FormErrors($formatter, $translator))->render($form));

        $form = new Form();
        $form->add(new $elementClass('checkbox', $options));
        $form->setData(['checkbox' => $elementClass === MultiCheckbox::class ? [$validValue] : $validValue]);
        self::assertTrue($form->isValid());
    }

    public static function checkboxes(): array
    {
        return [
            [CreatePermit::class, 'declaration', SingleCheckbox::class, 'checkbox.error.permit-declaration', '1'],
            [CreatePermit::class, 'checked', SingleCheckbox::class, 'checkbox.error.permit-checked', '1'],
            [Bottom::class, 'declaration', SingleCheckbox::class, 'checkbox.error.permit-declaration', '1'],
            [Bottom::class, 'checked', SingleCheckbox::class, 'checkbox.error.permit-checked', '1'],
            [BilateralBottom::class, 'declaration', SingleCheckbox::class, 'checkbox.error.permit-declaration', '1'],
            [TransportManagerMerge::class, 'confirm', SingleCheckbox::class, 'checkbox.error.tm-merge', 'Y'],
            [PublicHoliday::class, 'areas', MultiCheckbox::class, 'checkbox.error.public-holiday-areas', 'isEngland'],
            [CacheClear::class, 'cacheTypes', MultiCheckbox::class, 'checkbox.error.cache-types', 'translations'],
        ];
    }
}
