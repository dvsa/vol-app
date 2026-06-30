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
class AriaAttributeTranslationTest extends MockeryTestCase
{
    /**
     * @dataProvider helperProvider
     */
    public function testAriaAttributeValueIsTranslated(string $helperClass): void
    {
        /** @var AbstractHelper $sut */
        $sut = new $helperClass();
        $sut->addTranslatableAttributePrefix('aria-');
        $sut->setTranslator($this->makeTranslator());

        $output = $sut->createAttributesString(['aria-label' => 'aria.test.key']);

        self::assertStringContainsString('aria-label="TRANSLATED"', $output);
    }

    /**
     * @dataProvider helperProvider
     */
    public function testXPrefixedAttributeValueIsTranslated(string $helperClass): void
    {
        /** @var AbstractHelper $sut */
        $sut = new $helperClass();
        $sut->addTranslatableAttributePrefix('x-');
        $sut->setTranslator($this->makeTranslator());

        $output = $sut->createAttributesString(['x-custom-attr' => 'x.test.key']);

        self::assertStringContainsString('x-custom-attr="TRANSLATED"', $output);
    }

    public static function helperProvider(): array
    {
        return [
            'FormButton'        => [FormButton::class],
            'FormCaptcha'       => [FormCaptcha::class],
            'FormCheckbox'      => [FormCheckbox::class],
            'FormCollection'    => [FormCollection::class],
            'FormColor'         => [FormColor::class],
            'FormDate'          => [FormDate::class],
            'FormDateSelect'    => [FormDateSelect::class],
            'FormDateTime'      => [FormDateTime::class],
            'FormDateTimeLocal' => [FormDateTimeLocal::class],
            'FormDateTimeSelect' => [FormDateTimeSelect::class],
            'FormEmail'         => [FormEmail::class],
            'FormFile'          => [FormFile::class],
            'FormHidden'        => [FormHidden::class],
            'FormImage'         => [FormImage::class],
            'FormInput'         => [FormInput::class],
            'FormLabel'         => [FormLabel::class],
            'FormMonth'         => [FormMonth::class],
            'FormMonthSelect'   => [FormMonthSelect::class],
            'FormMultiCheckbox' => [FormMultiCheckbox::class],
            'FormNumber'        => [FormNumber::class],
            'FormPassword'      => [FormPassword::class],
            'FormRadio'         => [FormRadio::class],
            'FormRange'         => [FormRange::class],
            'FormReset'         => [FormReset::class],
            'FormRow'           => [FormRow::class],
            'FormSearch'        => [FormSearch::class],
            'FormSelect'        => [FormSelect::class],
            'FormSubmit'        => [FormSubmit::class],
            'FormTel'           => [FormTel::class],
            'FormText'          => [FormText::class],
            'FormTextarea'      => [FormTextarea::class],
            'FormTime'          => [FormTime::class],
            'FormUrl'           => [FormUrl::class],
            'FormWeek'          => [FormWeek::class],
        ];
    }

    private function makeTranslator(): TranslatorInterface
    {
        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->andReturn('TRANSLATED');
        return $translator;
    }
}
