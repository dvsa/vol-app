<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper;
use Common\Form\View\Helper\Form as FormViewHelper;
use Common\Form\View\Helper\FormCollection;
use Laminas\Form\Element\Text;
use Laminas\Stdlib\PriorityList;
use Laminas\View\Helper\Doctype;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Element;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

/**
 * @covers \Common\Form\View\Helper\Form
 */
class FormTest extends TestCase
{
    public $docType;
    /** @var \Laminas\Form\Form */
    protected $form;

    #[\Override]
    protected function setUp(): void
    {
        $this->form = new \Laminas\Form\Form('test');
        $this->docType = m::mock(Doctype::class);
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderFormWithoutAction(): void
    {
        $_SERVER['REQUEST_URI'] = 'bar';
        $this->form->add(new Text('test'));
        $this->docType->expects('getDoctype')->andReturn('doctype');

        $container = m::mock(ContainerInterface::class);

        $helpers = new HelperPluginManager($container);
        $helpers->setService('formrow', new Helper\FormRow([]));
        $helpers->setService('formCollection', new FormCollection());
        $helpers->setService('addTags', new \Common\View\Helper\AddTags());
        $helpers->setService(Doctype::class, $this->docType);

        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);

        $viewHelper = new FormViewHelper();
        $viewHelper->setView($view);
        echo $viewHelper($this->form, 'form', '/');

        $this->expectOutputRegex(
            '/^<form action="bar" method="(POST|GET)" name="test" id="test"><div class="field "><\/div><\/form>$/'
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderFormWithElement(): void
    {
        $this->form->add(new Text('test'));
        $this->form->setAttribute('action', 'foo');
        $this->docType->expects('getDoctype')->andReturn('doctype');

        $container = m::mock(ContainerInterface::class);
        $helpers = new HelperPluginManager($container);
        $helpers->setService('formrow', new Helper\FormRow([]));
        $helpers->setService('formCollection', new FormCollection());
        $helpers->setService('addTags', new \Common\View\Helper\AddTags());
        $helpers->setService(Doctype::class, $this->docType);

        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);

        $viewHelper = new FormViewHelper();
        $viewHelper->setView($view);
        echo $viewHelper($this->form, 'form', '/');

        $this->expectOutputRegex(
            '/^<form action="foo" method="(POST|GET)" name="test" id="test"><div class="field "><\/div><\/form>$/'
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderFormWithFieldset(): void
    {
        $this->form->add(new \Laminas\Form\Fieldset('test'));
        $this->form->setAttribute('action', 'foo');
        $this->docType->expects('getDoctype')->andReturn('doctype');

        $container = m::mock(ContainerInterface::class);
        $helpers = new HelperPluginManager($container);
        $helpers->setService('formCollection', new FormCollection());
        $helpers->setService('formrow', new Helper\FormRow([]));
        $helpers->setService('addTags', new \Common\View\Helper\AddTags());
        $helpers->setService(Doctype::class, $this->docType);

        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);

        $viewHelper = new FormViewHelper();
        $viewHelper->setView($view);
        echo $viewHelper($this->form, 'form', '/');

        $this->expectOutputRegex('/^<form action="(.*)" method="(POST|GET)" name="test" id="test"><\/form>$/');
    }

    public function testReadonly(): void
    {
        $mockElement = m::mock(\Laminas\Form\ElementInterface::class);
        $mockElement->shouldReceive('getName')->andReturn('name');

        $mockHelper = m::mock(FormCollection::class);
        $mockHelper->shouldReceive('setReadOnly')->once()->with(true);
        $mockHelper->shouldReceive('__invoke')->with($mockElement)->andReturn('element');

        $iterator = new PriorityList();
        $iterator->insert('array_key', $mockElement);

        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldReceive('prepare');
        $mockForm->shouldReceive('getIterator')->andReturn($iterator);
        $mockForm->shouldReceive('getOption')->with('readonly')->andReturn(true);
        $mockForm->shouldReceive('getAttributes')->andReturn([]);
        $mockForm->shouldReceive('getAttribute')->with('action')->once()->andReturn('foo');

        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('formCollection')->andReturn($mockHelper);
        $mockView->shouldReceive('plugin')->with('readonlyformrow')->andReturn($mockHelper);

        $sut = new FormViewHelper();
        $sut->setView($mockView);

        $sut($mockForm);
    }

    public function testRenderKeepEmptyFields(): void
    {
        //  check keepEmptyFieldset element
        $mockFieldsetKeepEmpty = m::mock(FieldsetInterface::class)
            ->shouldReceive('hasAttribute')->once()->with('keepEmptyFieldset')->andReturn(true)
            ->shouldReceive('getAttribute')->once()->with('keepEmptyFieldset')->andReturn(false)
            ->shouldReceive('count')->once()->andReturn(0)
            ->getMock();

        $mockHelper = m::mock(FormCollection::class)
            ->shouldReceive('setReadOnly')->with(false)->once()->getMock();

        $iterator = new PriorityList();
        $iterator->insert('array_key', $mockFieldsetKeepEmpty);

        $mockForm = m::mock(\Laminas\Form\Form::class)
            ->shouldReceive('prepare')->once()->andReturnNull()
            ->shouldReceive('getIterator')->once()->andReturn($iterator)
            ->shouldReceive('getOption')->twice()->with('readonly')->andReturn(false)
            ->shouldReceive('getAttributes')->once()->andReturn([])
            ->shouldReceive('getAttribute')->with('action')->once()->andReturn('foo')
            ->getMock();

        /** @var RendererInterface|m\MockInterface $mockView */
        $mockView = m::mock(RendererInterface::class)
            ->shouldReceive('formCollection')->once()->andReturn($mockHelper)
            ->shouldReceive('plugin')->once()->with('formrow')->andReturn($mockHelper)
            ->shouldReceive('addTags')->never()
            ->getMock();

        $sut = new FormViewHelper();
        $sut->setView($mockView);

        $sut($mockForm);
    }

    public function testRenderFieldsetsWithHidden(): void
    {
        $mockElmHidden = m::mock(Element\Hidden::class);
        $mockElm = m::mock(ElementInterface::class);

        // Check Fieldset with usual element
        $mockFsWithElement = m::mock(FieldsetInterface::class)
            ->shouldReceive('hasAttribute')->once()->with('keepEmptyFieldset')->andReturn(false)
            ->shouldReceive('count')->once()->andReturn(1)
            ->shouldReceive('getElements')->once()->with()->andReturn([$mockElm])
            ->shouldReceive('has')->once()->with('rows')->andReturn(false)
            ->getMock();

        // Check Fieldset with Fieldset with usual element
        $mockSubFs = m::mock(FieldsetInterface::class)
            ->shouldReceive('getElements')->once()->with()->andReturn([$mockElm])
            ->getMock();

        $mockFsWithSubFs = m::mock(FieldsetInterface::class)
            ->shouldReceive('getElements')->once()->with()->andReturn([])
            ->shouldReceive('hasAttribute')->once()->with('keepEmptyFieldset')->andReturn(false)
            ->shouldReceive('count')->once()->andReturn(1)
            ->shouldReceive('getFieldsets')->once()->with()->andReturn([$mockSubFs])
            ->shouldReceive('has')->once()->with('rows')->andReturn(false)
            ->getMock();

        //  check Fieldset with Hidden element
        $mockFsWithHiddenElement = m::mock(FieldsetInterface::class)
            ->shouldReceive('hasAttribute')->once()->with('keepEmptyFieldset')->andReturn(false)
            ->shouldReceive('count')->once()->andReturn(1)
            ->shouldReceive('getElements')->once()->with()->andReturn([$mockElmHidden])
            ->shouldReceive('setAttribute')->once()->with('class', 'hidden')
            ->shouldReceive('getFieldsets')->once()->with()->andReturn([])
            ->shouldReceive('has')->once()->with('rows')->andReturn(false)
            ->getMock();

        $mockHelper = m::mock(FormCollection::class)
            ->shouldReceive('setReadOnly')
            ->with(false)
            ->once()
            ->getMock();

        $iterator = new PriorityList();
        $iterator->insert('array_key1', $mockFsWithElement);
        $iterator->insert('array_key2', $mockFsWithSubFs);
        $iterator->insert('array_key3', $mockFsWithHiddenElement);

        $mockForm = m::mock(\Laminas\Form\Form::class)
            ->shouldReceive('prepare')->once()->andReturnNull()
            ->shouldReceive('getIterator')->once()->andReturn($iterator)
            ->shouldReceive('getOption')->twice()->with('readonly')->andReturn(false)
            ->shouldReceive('getAttributes')->once()->andReturn([])
            ->shouldReceive('getAttribute')->with('action')->once()->andReturn('foo')
            ->getMock();

        /** @var RendererInterface|m\MockInterface $mockView */
        $mockView = m::mock(RendererInterface::class)
            ->shouldReceive('formCollection')->times(4)->andReturn($mockHelper)
            ->shouldReceive('plugin')->once()->with('formrow')->andReturn($mockHelper)
            ->shouldReceive('addTags')->times(3)
            ->getMock();

        $sut = new FormViewHelper();
        $sut->setView($mockView);

        $sut($mockForm);
    }

    public function testRenderFieldsetsWithTable(): void
    {
        // Mock rows element
        $mockRowsElm = m::mock(ElementInterface::class)
            ->shouldReceive('getMessages')->twice()->andReturn([ 'required' => 'test' ])
            ->shouldReceive('setMessages')->once()->with([])->andReturn(true)
            ->getMock();

        // Mock table element
        $mockTableElement = m::mock(ElementInterface::class)
            ->shouldReceive('setMessages')->once()->with([ 'required' => 'test' ])->andReturn(true)
            ->getMock();

        $mockFsWithTableElement = m::mock(FieldsetInterface::class)
            ->shouldReceive('getElements')->once()->with()->andReturn([$mockRowsElm])
            ->shouldReceive('hasAttribute')->once()->with('keepEmptyFieldset')->andReturn(false)
            ->shouldReceive('count')->once()->andReturn(1)
            ->shouldReceive('has')->once()->with('rows')->andReturn(true)
            ->shouldReceive('get')->with('rows')->andReturn($mockRowsElm)
            ->shouldReceive('get')->once()->with('table')->andReturn($mockTableElement)
            ->getMock();

        $mockHelper = m::mock(FormCollection::class)
            ->shouldReceive('setReadOnly')
            ->with(false)
            ->once()
            ->getMock();

        $iterator = new PriorityList();
        $iterator->insert('array_key', $mockFsWithTableElement);

        $mockForm = m::mock(\Laminas\Form\Form::class)
            ->shouldReceive('prepare')->once()->andReturnNull()
            ->shouldReceive('getIterator')->once()->andReturn($iterator)
            ->shouldReceive('getOption')->twice()->with('readonly')->andReturn(false)
            ->shouldReceive('getAttributes')->once()->andReturn([])
            ->shouldReceive('getAttribute')->with('action')->once()->andReturn('foo')
            ->getMock();

        /** @var RendererInterface|m\MockInterface $mockView */
        $mockView = m::mock(RendererInterface::class)
            ->shouldReceive('formCollection')->times(2)->andReturn($mockHelper)
            ->shouldReceive('plugin')->once()->with('formrow')->andReturn($mockHelper)
            ->shouldReceive('addTags')->times(1)
            ->getMock();

        $sut = new FormViewHelper();
        $sut->setView($mockView);

        $sut($mockForm);
    }
}
