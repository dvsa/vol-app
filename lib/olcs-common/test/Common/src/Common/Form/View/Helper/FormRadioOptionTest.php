<?php

namespace CommonTest\Form\View\Helper;

use Laminas\Form\View\Helper\FormLabel;
use Common\Form\View\Helper\FormRadioOption;
use Common\View\Helper\UniqidGenerator;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Element\Radio;
use Psr\Container\ContainerInterface;

class FormRadioOptionTest extends TestCase
{
    public function testInvokeNull(): void
    {
        $sut = new FormRadioOption();
        $this->assertSame($sut, $sut->__invoke());
    }

    public function testInvoke(): void
    {
        $idGenerator = m::mock(UniqidGenerator::class);
        $idGenerator->shouldReceive('generateId')->twice()->andReturn('generated_id');
        $sut = new FormRadioOption($idGenerator);
        $radioElement = new Radio('NAME');
        $radioElement->setValueOptions(['A' => 'aaa', 'B' => 'bbb']);

        $view = $this->createPartialMock(PhpRenderer::class, []);
        $container = m::mock(ContainerInterface::class)->shouldIgnoreMissing();

        $helpers = new HelperPluginManager($container);
        $helpers->setService('form_label', new FormLabel());

        $view->setHelperPluginManager($helpers);
        $sut->setView($view);

        $rendered = $sut->__invoke($radioElement, 'B');
        $this->assertSame(
            '<div class="govuk-radios"><div class="govuk-radios__item"><input type="radio" name="NAME" class="govuk-radios__input" value="B" id="generated_id"><label class="govuk-label&#x20;govuk-radios__label" for="generated_id">bbb</label></div></div>',
            $rendered
        );
        $rendered = $sut->__invoke($radioElement, 'A');
        $this->assertSame(
            '<div class="govuk-radios"><div class="govuk-radios__item"><input type="radio" name="NAME" class="govuk-radios__input" value="A" id="generated_id"><label class="govuk-label&#x20;govuk-radios__label" for="generated_id">aaa</label></div></div>',
            $rendered
        );
    }
}
