<?php

declare(strict_types=1);

namespace CommonTest\Common\Form\View\Helper\Extended;

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\View\Helper\FormButton;
use Laminas\Form\View\Helper\FormCaptcha;
use Laminas\Form\View\Helper\FormCheckbox;
use Laminas\Form\View\Helper\FormCollection;
use Laminas\Form\View\Helper\FormColor;
use Laminas\Form\View\Helper\FormDate;
use Laminas\Form\View\Helper\FormDateSelect;
use Laminas\Form\View\Helper\FormDateTime;
use Laminas\Form\View\Helper\FormDateTimeLocal;
use Laminas\Form\View\Helper\FormDateTimeSelect;
use Laminas\Form\View\Helper\FormEmail;
use Laminas\Form\View\Helper\FormFile;
use Laminas\Form\View\Helper\FormHidden;
use Laminas\Form\View\Helper\FormImage;
use Laminas\Form\View\Helper\FormInput;
use Laminas\Form\View\Helper\FormLabel;
use Laminas\Form\View\Helper\FormMonth;
use Laminas\Form\View\Helper\FormMonthSelect;
use Laminas\Form\View\Helper\FormMultiCheckbox;
use Laminas\Form\View\Helper\FormNumber;
use Laminas\Form\View\Helper\FormPassword;
use Common\Form\View\Helper\FormRadio;
use Laminas\Form\View\Helper\FormRange;
use Laminas\Form\View\Helper\FormReset;
use Laminas\Form\View\Helper\FormRow;
use Laminas\Form\View\Helper\FormSearch;
use Laminas\Form\View\Helper\FormSelect;
use Laminas\Form\View\Helper\FormSubmit;
use Laminas\Form\View\Helper\FormTel;
use Laminas\Form\View\Helper\FormText;
use Laminas\Form\View\Helper\FormTextarea;
use Laminas\Form\View\Helper\FormTime;
use Laminas\Form\View\Helper\FormUrl;
use Laminas\Form\View\Helper\FormWeek;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Verifies that aria-* and x-* attribute values are passed through the translator
 * when building the HTML attribute string, using the TranslatableAttributePrefixInitializer
 * approach (addTranslatableAttributePrefix) rather than the removed PrepareAttributesTrait.
 */
final class AriaAttributeTranslationTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('helperProvider')]
    public function testAriaAttributeValueIsTranslated(string $helperClass): void
    {
        /** @var AbstractHelper $sut */
        $sut = new $helperClass();
        $sut->addTranslatableAttributePrefix('aria-');
        $sut->setTranslator($this->makeTranslator());

        $output = $sut->createAttributesString(['aria-label' => 'aria.test.key']);

        $this->assertStringContainsString('aria-label="TRANSLATED"', $output);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('helperProvider')]
    public function testXPrefixedAttributeValueIsTranslated(string $helperClass): void
    {
        /** @var AbstractHelper $sut */
        $sut = new $helperClass();
        $sut->addTranslatableAttributePrefix('x-');
        $sut->setTranslator($this->makeTranslator());

        $output = $sut->createAttributesString(['x-custom-attr' => 'x.test.key']);

        $this->assertStringContainsString('x-custom-attr="TRANSLATED"', $output);
    }

    public static function helperProvider(): \Iterator
    {
        yield 'FormButton' => [FormButton::class];
        yield 'FormCaptcha' => [FormCaptcha::class];
        yield 'FormCheckbox' => [FormCheckbox::class];
        yield 'FormCollection' => [FormCollection::class];
        yield 'FormColor' => [FormColor::class];
        yield 'FormDate' => [FormDate::class];
        yield 'FormDateSelect' => [FormDateSelect::class];
        yield 'FormDateTime' => [FormDateTime::class];
        yield 'FormDateTimeLocal' => [FormDateTimeLocal::class];
        yield 'FormDateTimeSelect' => [FormDateTimeSelect::class];
        yield 'FormEmail' => [FormEmail::class];
        yield 'FormFile' => [FormFile::class];
        yield 'FormHidden' => [FormHidden::class];
        yield 'FormImage' => [FormImage::class];
        yield 'FormInput' => [FormInput::class];
        yield 'FormLabel' => [FormLabel::class];
        yield 'FormMonth' => [FormMonth::class];
        yield 'FormMonthSelect' => [FormMonthSelect::class];
        yield 'FormMultiCheckbox' => [FormMultiCheckbox::class];
        yield 'FormNumber' => [FormNumber::class];
        yield 'FormPassword' => [FormPassword::class];
        yield 'FormRadio' => [FormRadio::class];
        yield 'FormRange' => [FormRange::class];
        yield 'FormReset' => [FormReset::class];
        yield 'FormRow' => [FormRow::class];
        yield 'FormSearch' => [FormSearch::class];
        yield 'FormSelect' => [FormSelect::class];
        yield 'FormSubmit' => [FormSubmit::class];
        yield 'FormTel' => [FormTel::class];
        yield 'FormText' => [FormText::class];
        yield 'FormTextarea' => [FormTextarea::class];
        yield 'FormTime' => [FormTime::class];
        yield 'FormUrl' => [FormUrl::class];
        yield 'FormWeek' => [FormWeek::class];
    }

    private function makeTranslator(): TranslatorInterface
    {
        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->andReturn('TRANSLATED');
        return $translator;
    }
}
