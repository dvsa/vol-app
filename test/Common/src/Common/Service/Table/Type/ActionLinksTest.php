<?php

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Common\Util\Escape;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\ActionLinks;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator as Translator;

class ActionLinksTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->sm = new ServiceManager();

        $this->table = m::mock(TableBuilder::class);
        $this->table->expects('getServiceLocator')
            ->andReturn($this->sm);

        $this->sut = new ActionLinks($this->table);
    }

    public function testRender(): void
    {
        $mockTranslate = $this->getTranslator();

        $this->sm->setService('translator', $mockTranslate);

        $column = [
            'deleteInputName' => 'table[action][delete][%d]',
            'replaceInputName' => 'table[action][replace][%d]',
            'isRemoveVisible' => /**
             * @return true
             */
            static fn($data): bool => true,
            'isReplaceVisible' => /**
             * @return true
             */
            static fn($data): bool => true,
        ];
        $data = [
            'id' => 123
        ];

        $classes = Escape::htmlAttr('right-aligned govuk-button govuk-button--secondary trigger-modal');
        $nameRemove = Escape::htmlAttr('table[action][delete][123]');
        $ariaRemove = Escape::htmlAttr('Remove Aria (id 123)');
        $nameReplace = Escape::htmlAttr('table[action][replace][123]');
        $ariaReplace = Escape::htmlAttr('Replace Aria (id 123)');

        $expected = '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="' . $classes . '" ' .
            'name="' . $nameRemove . '" ' .
            'aria-label="' . $ariaRemove . '">Remove</button> <button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="' . $classes . '" ' .
            'name="' . $nameReplace . '" aria-label="' . $ariaReplace . '">Replace</button>';

        $this->assertEquals($expected, $this->sut->render($data, $column));
    }

    public function testRenderDefault(): void
    {
        $mockTranslate = $this->getTranslator();

        $this->sm->setService('translator', $mockTranslate);

        $column = [
            'replaceInputName' => 'table[action][replace][%d]',
        ];
        $data = [
            'id' => 123
        ];

        $classes = Escape::htmlAttr('right-aligned govuk-button govuk-button--secondary trigger-modal');
        $name = Escape::htmlAttr('table[action][delete][123]');
        $aria = Escape::htmlAttr('Remove Aria (id 123)');

        $expected = '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="' . $classes . '" ' .
            'name="' . $name . '" aria-label="' . $aria . '">Remove</button>';

        $this->assertEquals($expected, $this->sut->render($data, $column));
    }

    public function testRenderNoModal(): void
    {
        $mockTranslate = $this->getTranslator();

        $this->sm->setService('translator', $mockTranslate);

        $column = [
            'replaceInputName' => 'table[action][replace][%d]',
            'dontUseModal' => true,
        ];
        $data = [
            'id' => 123
        ];

        $classes = Escape::htmlAttr('right-aligned govuk-button govuk-button--secondary');
        $name = Escape::htmlAttr('table[action][delete][123]');
        $aria = Escape::htmlAttr('Remove Aria (id 123)');

        $expected = '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="' . $classes . '" ' .
            'name="' . $name . '" aria-label="' . $aria . '">Remove</button>';

        $this->assertEquals($expected, $this->sut->render($data, $column));
    }

    public function testRenderWithCustomActionClasses(): void
    {
        $mockTranslate = $this->getTranslator();

        $this->sm->setService('translator', $mockTranslate);

        $column = [
            'replaceInputName' => 'table[action][replace][%d]',
            'dontUseModal' => true,
            'actionClasses' => 'my-custom-class'
        ];
        $data = [
            'id' => 123
        ];

        $classes = Escape::htmlAttr('my-custom-class');
        $name = Escape::htmlAttr('table[action][delete][123]');
        $aria = Escape::htmlAttr('Remove Aria (id 123)');

        $expected = '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="' . $classes . '" ' .
            'name="' . $name . '" aria-label="' . $aria . '">Remove</button>';

        $this->assertEquals($expected, $this->sut->render($data, $column));
    }

    private function getTranslator(): m\MockInterface
    {
        $translator = m::mock(Translator::class);
        $translator->expects('translate')->with(ActionLinks::KEY_ACTION_LINKS_REMOVE)->andReturn('Remove');
        $translator->expects('translate')->with(ActionLinks::KEY_ACTION_LINKS_REMOVE_ARIA)->andReturn('Remove Aria');
        $translator->expects('translate')->with(ActionLinks::KEY_ACTION_LINKS_REPLACE)->andReturn('Replace');
        $translator->expects('translate')->with(ActionLinks::KEY_ACTION_LINKS_REPLACE_ARIA)->andReturn('Replace Aria');

        return $translator;
    }
}
