<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\InputFilters\ActionLink;
use Common\Form\Elements\Types\AttachFilesButton;
use Common\Form\Elements\Types\GuidanceTranslated;
use Common\Form\Elements\Types\Html;
use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Elements\Types\Table;
use Common\Form\Elements\Types\TermsBox;
use Common\Form\Elements\Types\TrafficAreaSet;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\View\Helper\FormElement;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormPlainText;
use CommonTest\Common\Util\DummyTranslator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Url;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\JsonRenderer;
use Laminas\Form\View\Helper;

class FormElementTest extends m\Adapter\Phpunit\MockeryTestCase
{
    protected const A_HINT = 'A HINT';

    protected const A_CUSTOM_CSS_CLASS = 'A_CSS_CLASS';

    /**
     * @var \Laminas\Form\Element
     */
    protected $element;

    /**
     * @param (mixed|string)[] $options
     *
     * @psalm-param array{route?: 'route', 'hint-below'?: 'HINT BELOW'|mixed, 'hint-class'?: mixed, 'hint-below-class'?: mixed} $options
     */
    private function prepareElement(string $type = 'Text', array $options = []): void
    {
        if (!str_contains($type, '\\')) {
            $type = '\Laminas\Form\Element\\' . ucfirst($type);
        }

        $options = array_merge(
            [
                'type' => $type,
                'label' => 'Label',
                'hint' => 'Hint',
            ],
            $options
        );

        $this->element = new $type('test');
        $this->element->setOptions($options);
        $this->element->setAttribute('class', 'class');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderWithNoRendererPlugin(): void
    {
        $this->prepareElement();
        $view = new JsonRenderer();

        $viewHelper = new FormElement();
        $viewHelper->setView($view);
        $viewHelper($this->element, 'formElement', '/');

        $this->expectOutputString('');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTextElement(): void
    {
        $this->prepareElement();

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex(
            '/^<p class="hint">(.*)<\/p><input type="text" name="(.*)" class="(.*)" id="(.*)" value="(.*)">$/'
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForPlainTextElement(): void
    {
        $this->prepareElement(\Common\Form\Elements\Types\PlainText::class);

        $viewHelper = $this->prepareViewHelper();
        $this->element->setValue('plain');

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^plain$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForActionLinkElementWithRoute(): void
    {
        $options = ['route' => 'route'];
        $this->prepareElement(ActionLink::class, $options);

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<a href=".*" class="(.*)">(.*)<\/a>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForActionLinkElementWithUrl(): void
    {
        $this->prepareElement(ActionLink::class);
        $this->element->setValue('url');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<a href="(.*)" class="(.*)">(.*)<\/a>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForActionLinkElementWithMaliciousUrl(): void
    {
        $this->prepareElement(ActionLink::class);
        $maliciousUrl = '<script>alert("url")</script>';
        $this->element->setValue($maliciousUrl);

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex(
            '/^<a href="' . preg_quote(
                htmlspecialchars($maliciousUrl, ENT_QUOTES, 'utf-8'),
                '/'
            ) . '" class="class">(.*)<\/a>$/'
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForActionLinkElementWithTarget(): void
    {
        $this->prepareElement(ActionLink::class);
        $this->element->setValue('url');
        $this->element->setAttribute('target', '_blank');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<a href="(.*)" class="(.*)" target="_blank">(.*)<\/a>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlElement(): void
    {
        $this->prepareElement(Html::class);
        $this->element->setValue('<div></div>');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div><\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTermsBoxElement(): void
    {
        $this->prepareElement(TermsBox::class);
        $this->element->setValue('foo');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div name="test" class="class&#x20;terms--box" id="test">foo<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTermsBoxElementWithoutClass(): void
    {
        $this->prepareElement(TermsBox::class);
        $this->element->setAttribute('class', null);
        $this->element->setValue('foo');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div name="test" class="&#x20;terms--box" id="test">foo<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElement(): void
    {
        $this->prepareElement(HtmlTranslated::class);
        $this->element->setValue('some-translation-key');

        $translations = ['some-translation-key' => 'actual translated string'];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^actual translated string$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElementWithoutValue(): void
    {
        $this->prepareElement(HtmlTranslated::class);

        $viewHelper = $this->prepareViewHelper([]);

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEmpty($markup);
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElementWithTokens(): void
    {
        $this->prepareElement(HtmlTranslated::class);
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setTokens(['foo-key', 'bar-key']);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div>foo string and then bar string<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElementWithTokensViaOptions(): void
    {
        $this->prepareElement(HtmlTranslated::class);
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setOptions(['tokens' => ['foo-key', 'bar-key']]);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div>foo string and then bar string<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTableElement(): void
    {
        $this->prepareElement(Table::class);

        $mockTable = $this->getMockBuilder(\Common\Service\Table\TableBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $mockTable->expects($this->any())
            ->method('render')
            ->will($this->returnValue('<table></table>'));

        $this->element->setTable($mockTable);

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<table><\/table>$/');
    }

    public function testRenderForTrafficAreaSet(): void
    {
        $this->prepareElement(TrafficAreaSet::class);

        $this->element
            ->setValue('<ABC>')
            ->setOption('hint-position', 'below');

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEquals(
            '<div class="label">&lt;ABC&gt;</div><div class="hint">Hint</div>',
            $markup
        );
    }

    public function testRenderForTrafficAreaSetWithoutHint(): void
    {
        $this->prepareElement(TrafficAreaSet::class);

        $this->element->setValue('ABC');
        $this->element->setOption('hint', null);

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEquals(
            '<div class="label">ABC</div>',
            $markup
        );
    }

    public function testRenderForTrafficAreaSetWithSuffix(): void
    {
        $this->prepareElement(TrafficAreaSet::class);

        $this->element->setValue('ABC');
        $this->element->setOption('hint-suffix', '-foo');

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEquals(
            '<p class="hint">Hint</p><div class="label">ABC</div>',
            $markup
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElement(): void
    {
        $this->prepareElement(GuidanceTranslated::class);
        $this->element->setValue('some-translation-key');

        $translations = ['some-translation-key' => 'actual translated string'];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div class="article">actual translated string<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElementWithoutValue(): void
    {
        $this->prepareElement(GuidanceTranslated::class);

        $viewHelper = $this->prepareViewHelper([]);

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEmpty($markup);
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElementWithTokens(): void
    {
        $this->prepareElement(GuidanceTranslated::class);
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setTokens(['foo-key', 'bar-key']);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div class="article"><div>foo string and then bar string<\/div><\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElementWithTokensViaOptions(): void
    {
        $this->prepareElement(GuidanceTranslated::class);
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setOptions(['tokens' => ['foo-key', 'bar-key']]);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div class="article"><div>foo string and then bar string<\/div><\/div>$/');
    }

    public function testRenderForAttachFilesButton(): void
    {
        $this->prepareElement(AttachFilesButton::class);

        $this->element->setValue('My Button');

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $expected = '<ul class="attach-action__list"><li class="attach-action"><label class="attach-action__label"> '
            . '<input type="file" name="test" class="class&#x20;attach-action__input" id="test">'
            . '</label>'
            . '<p class="attach-action__hint">Hint</p></li></ul>';

        $this->assertEquals(
            $expected,
            $markup
        );
    }

    public function testRenderForAttachFilesButtonWithNoClass(): void
    {
        $this->prepareElement(AttachFilesButton::class);

        $this->element->setValue('My Button');
        $this->element->setAttribute('class', null);

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $expected = '<ul class="attach-action__list"><li class="attach-action"><label class="attach-action__label"> '
            . '<input type="file" name="test" class="&#x20;attach-action__input" id="test">'
            . '</label>'
            . '<p class="attach-action__hint">Hint</p></li></ul>';

        $this->assertEquals(
            $expected,
            $markup
        );
    }

    public function testRenderHintBelow(): void
    {
        $this->prepareElement('Text', ['hint-below' => 'HINT BELOW']);

        $viewHelper = $this->prepareViewHelper();

        $output = $viewHelper($this->element, 'formCollection', '/');

        $this->assertSame(
            '<p class="hint">Hint</p><input type="text" name="test" class="class" id="test" value="">'
            . '<div class="hint">HINT BELOW</div>',
            $output
        );
    }

    /**
     * @depends testRenderHintBelow
     */
    public function testRenderAddsCustomHintClassToHintsThatArePositionedBelowAnElement(): void
    {
        // Setup
        $this->prepareElement('Text', [
            'hint-below' => static::A_HINT,
            'hint-class' => static::A_CUSTOM_CSS_CLASS,
        ]);
        $viewHelper = $this->prepareViewHelper();

        // Execute
        $output = $viewHelper($this->element, 'formCollection', '/');

        // Assert
        $this->assertSame(
            sprintf(
                '<p class="A_CSS_CLASS">Hint</p><input type="text" name="test" class="class" id="test" value=""><div class="%s">%s</div>',
                static::A_CUSTOM_CSS_CLASS,
                static::A_HINT
            ),
            $output
        );
    }

    /**
     * @depends testRenderAddsCustomHintClassToHintsThatArePositionedBelowAnElement
     */
    public function testRenderAddsCustomBelowHintClassToHintsThatArePositionedBelowAnElement(): void
    {
        // Setup
        $this->prepareElement('Text', [
            'hint-below' => static::A_HINT,
            'hint-below-class' => static::A_CUSTOM_CSS_CLASS,
        ]);
        $viewHelper = $this->prepareViewHelper();

        // Execute
        $output = $viewHelper($this->element, 'formCollection', '/');

        // Assert
        $this->assertSame(
            sprintf(
                '<p class="hint">Hint</p><input type="text" name="test" class="class" id="test" value=""><div class="%s">%s</div>',
                static::A_CUSTOM_CSS_CLASS,
                static::A_HINT
            ),
            $output
        );
    }

    public function testRenderElementWithError(): void
    {
        $this->prepareElement();
        $this->element->setMessages(['Message 1']);
        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertSame(
            '<p class="hint">Hint</p><p class="govuk-error-message"><span class="govuk-visually-hidden">Error:</span>Message 1</p><input type="text" name="test" class="class&#x20;error__input" id="test" value="">',
            $markup
        );
    }

    private function prepareViewHelper(array|null $translateMap = null): FormElement
    {
        $translator = new DummyTranslator();
        if (!is_null($translateMap)) {
            $translator->setMap($translateMap);
        }

        $translateHelper = new Translate();
        $translateHelper->setTranslator($translator);

        /** @var PhpRenderer | MockObject $view */
        $view = $this->createPartialMock(PhpRenderer::class, []);

        $plainTextService = new FormPlainText();
        $plainTextService->setTranslator($translator);
        $plainTextService->setView($view);

        $urlHelper = m::mock(Url::class);
        $urlHelper->shouldReceive('__invoke')->andReturn('url');

        $docType = m::mock(Doctype::class);

        $formElementErrors = new FormElementErrors(new FormElementMessageFormatter($translator), $translator);
        $formElementErrors->setView($view);

        $container = m::mock(ContainerInterface::class);

        $helpers = new HelperPluginManager($container);
        $helpers->setService('formtext', new Helper\FormText());
        $helpers->setService('formfile', new Helper\FormFile());
        $helpers->setService('translate', $translateHelper);
        $helpers->setService('form_plain_text', $plainTextService);
        $helpers->setService('form', new Helper\Form());
        $helpers->setService('url', $urlHelper);
        $helpers->setService('form_element_errors', $formElementErrors);
        $helpers->setService('doctype', $docType);

        $view->setHelperPluginManager($helpers);

        $viewHelper = new FormElement();
        $viewHelper->setView($view);

        return $viewHelper;
    }
}
