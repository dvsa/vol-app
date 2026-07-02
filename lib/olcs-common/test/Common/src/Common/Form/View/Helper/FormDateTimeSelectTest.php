<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormDateTimeSelect;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\DateTimeSelect;
use Laminas\Form\Element\Text;
use Laminas\Form\View\Helper\FormInput;
use Laminas\Form\View\Helper\FormSelect;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\Service\ViewHelperManagerFactory;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

/**
 * Form Date Select Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormDateTimeSelectTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $formInput = m::mock(FormInput::class)->makePartial();
        $formSelect = m::mock(FormSelect::class)->makePartial();

        $translator = m::mock(Translator::class);
        $translator->shouldReceive('translate')->andReturnUsing(
            static fn($key) => 'translated-' . $key
        );

        $container = m::mock(ContainerInterface::class);
        $helpers = new HelperPluginManager($container);
        $helpers->setService('forminput', $formInput);
        $helpers->setService('formselect', $formSelect);

        /** @var PhpRenderer $view */
        $view = m::mock(PhpRenderer::class)->makePartial();
        $view->setHelperPluginManager($helpers);

        $this->sut = new FormDateTimeSelect();
        $this->sut->setView($view);
        $this->sut->setTranslator($translator);
    }

    public function testRender(): void
    {
        $element = new DateTimeSelect('date');
        $element->setOption('pattern', "d MMMM y 'time' HH:mm:ss");

        $markup = $this->sut->render($element);

        $expected = '<div class="field inline-text"><label for="_day">translated-date-Day</label>'
            . '<input type="select" name="day" id="_day" maxlength="2" value="">'
        . '</div> '
        . '<div class="field inline-text">'
            . '<label for="_month">translated-date-Month</label>'
            . '<input type="select" name="month" id="_month" maxlength="2" value="">'
        . '</div> '
        . '<div class="field inline-text">'
            . '<label for="_year">translated-date-Year</label>'
            . '<input type="select" name="year" id="_year" maxlength="4" value="">'
        . '</div>'
        . ' time <select name="hour" id="_hour">'
            . '<option value="00">00</option>'
            . '<option value="01">01</option>'
            . '<option value="02">02</option>'
            . '<option value="03">03</option>'
            . '<option value="04">04</option>'
            . '<option value="05">05</option>'
            . '<option value="06">06</option>'
            . '<option value="07">07</option>'
            . '<option value="08">08</option>'
            . '<option value="09">09</option>'
            . '<option value="10">10</option>'
            . '<option value="11">11</option>'
            . '<option value="12">12</option>'
            . '<option value="13">13</option>'
            . '<option value="14">14</option>'
            . '<option value="15">15</option>'
            . '<option value="16">16</option>'
            . '<option value="17">17</option>'
            . '<option value="18">18</option>'
            . '<option value="19">19</option>'
            . '<option value="20">20</option>'
            . '<option value="21">21</option>'
            . '<option value="22">22</option>'
            . '<option value="23">23</option>'
            . '</select>'
            . ':<select name="minute" id="_minute">'
            . '<option value="00">00</option>'
            . '<option value="15">15</option>'
            . '<option value="30">30</option>'
            . '<option value="45">45</option>'
        . '</select>';

        $this->assertEquals($expected, str_replace("\n", '', $markup));
    }

    public function testRenderWithAllMinutes(): void
    {
        $element = new DateTimeSelect('date');
        $element->setOption('pattern', "d MMMM y 'time' HH:mm:ss");
        $element->setOption('display_every_minute', true);

        $markup = $this->sut->render($element);

        $expected = '<div class="field inline-text"><label for="_day">translated-date-Day</label>'
            . '<input type="select" name="day" id="_day" maxlength="2" value="">'
            . '</div> '
            . '<div class="field inline-text">'
            . '<label for="_month">translated-date-Month</label>'
            . '<input type="select" name="month" id="_month" maxlength="2" value="">'
            . '</div> '
            . '<div class="field inline-text">'
            . '<label for="_year">translated-date-Year</label>'
            . '<input type="select" name="year" id="_year" maxlength="4" value="">'
            . '</div>'
            . ' time <select name="hour" id="_hour">'
            . '<option value="00">00</option>'
            . '<option value="01">01</option>'
            . '<option value="02">02</option>'
            . '<option value="03">03</option>'
            . '<option value="04">04</option>'
            . '<option value="05">05</option>'
            . '<option value="06">06</option>'
            . '<option value="07">07</option>'
            . '<option value="08">08</option>'
            . '<option value="09">09</option>'
            . '<option value="10">10</option>'
            . '<option value="11">11</option>'
            . '<option value="12">12</option>'
            . '<option value="13">13</option>'
            . '<option value="14">14</option>'
            . '<option value="15">15</option>'
            . '<option value="16">16</option>'
            . '<option value="17">17</option>'
            . '<option value="18">18</option>'
            . '<option value="19">19</option>'
            . '<option value="20">20</option>'
            . '<option value="21">21</option>'
            . '<option value="22">22</option>'
            . '<option value="23">23</option>'
            . '</select>'
            . ':<select name="minute" id="_minute">';

        for ($i = 0; $i <= 59; ++$i) {
            $minute = str_pad($i, 2, '0', STR_PAD_LEFT);
            $expected .= '<option value="' . $minute . '">' . $minute . '</option>';
        }

        $expected .= '</select>';

        $this->assertEquals($expected, str_replace("\n", '', $markup));
    }

    public function testRenderShouldCreateEmptyWithSeconds(): void
    {
        $element = new DateTimeSelect('date');
        $element->setShouldCreateEmptyOption(true);
        $element->setShouldShowSeconds(true);
        $element->setOption('pattern', "d MMMM y 'time' HH:mm:ss");

        $markup = $this->sut->render($element);

        $expected = '<div class="field inline-text"><label for="_day">translated-date-Day</label>'
            . '<input type="select" name="day" id="_day" maxlength="2" value="">'
            . '</div> '
            . '<div class="field inline-text">'
            . '<label for="_month">translated-date-Month</label>'
            . '<input type="select" name="month" id="_month" maxlength="2" value="">'
            . '</div> '
            . '<div class="field inline-text">'
            . '<label for="_year">translated-date-Year</label>'
            . '<input type="select" name="year" id="_year" maxlength="4" value="">'
            . '</div>'
            . ' time <select name="hour" id="_hour">'
            . '<option value=""></option>'
            . '<option value="00">00</option>'
            . '<option value="01">01</option>'
            . '<option value="02">02</option>'
            . '<option value="03">03</option>'
            . '<option value="04">04</option>'
            . '<option value="05">05</option>'
            . '<option value="06">06</option>'
            . '<option value="07">07</option>'
            . '<option value="08">08</option>'
            . '<option value="09">09</option>'
            . '<option value="10">10</option>'
            . '<option value="11">11</option>'
            . '<option value="12">12</option>'
            . '<option value="13">13</option>'
            . '<option value="14">14</option>'
            . '<option value="15">15</option>'
            . '<option value="16">16</option>'
            . '<option value="17">17</option>'
            . '<option value="18">18</option>'
            . '<option value="19">19</option>'
            . '<option value="20">20</option>'
            . '<option value="21">21</option>'
            . '<option value="22">22</option>'
            . '<option value="23">23</option>'
            . '</select>'
            . ':<select name="minute" id="_minute">'
            . '<option value=""></option>'
            . '<option value="00">00</option>'
            . '<option value="15">15</option>'
            . '<option value="30">30</option>'
            . '<option value="45">45</option>'
            . '</select>'
            . ':<select name="second" id="_second">'
            . '<option value=""></option>'
            . '<option value="00">00</option>'
            . '<option value="01">01</option>'
            . '<option value="02">02</option>'
            . '<option value="03">03</option>'
            . '<option value="04">04</option>'
            . '<option value="05">05</option>'
            . '<option value="06">06</option>'
            . '<option value="07">07</option>'
            . '<option value="08">08</option>'
            . '<option value="09">09</option>'
            . '<option value="10">10</option>'
            . '<option value="11">11</option>'
            . '<option value="12">12</option>'
            . '<option value="13">13</option>'
            . '<option value="14">14</option>'
            . '<option value="15">15</option>'
            . '<option value="16">16</option>'
            . '<option value="17">17</option>'
            . '<option value="18">18</option>'
            . '<option value="19">19</option>'
            . '<option value="20">20</option>'
            . '<option value="21">21</option>'
            . '<option value="22">22</option>'
            . '<option value="23">23</option>'
            . '<option value="24">24</option>'
            . '<option value="25">25</option>'
            . '<option value="26">26</option>'
            . '<option value="27">27</option>'
            . '<option value="28">28</option>'
            . '<option value="29">29</option>'
            . '<option value="30">30</option>'
            . '<option value="31">31</option>'
            . '<option value="32">32</option>'
            . '<option value="33">33</option>'
            . '<option value="34">34</option>'
            . '<option value="35">35</option>'
            . '<option value="36">36</option>'
            . '<option value="37">37</option>'
            . '<option value="38">38</option>'
            . '<option value="39">39</option>'
            . '<option value="40">40</option>'
            . '<option value="41">41</option>'
            . '<option value="42">42</option>'
            . '<option value="43">43</option>'
            . '<option value="44">44</option>'
            . '<option value="45">45</option>'
            . '<option value="46">46</option>'
            . '<option value="47">47</option>'
            . '<option value="48">48</option>'
            . '<option value="49">49</option>'
            . '<option value="50">50</option>'
            . '<option value="51">51</option>'
            . '<option value="52">52</option>'
            . '<option value="53">53</option>'
            . '<option value="54">54</option>'
            . '<option value="55">55</option>'
            . '<option value="56">56</option>'
            . '<option value="57">57</option>'
            . '<option value="58">58</option>'
            . '<option value="59">59</option>'
            . '</select>';

        $this->assertEquals($expected, str_replace("\n", '', $markup));
    }

    public function testRenderWrongElement(): void
    {
        $this->expectException(\Laminas\Form\Exception\InvalidArgumentException::class);

        $element = new Text('date');

        $this->sut->render($element);
    }

    public function testRenderElementWithNoName(): void
    {
        $this->expectException(\Laminas\Form\Exception\DomainException::class);

        $element = new DateTimeSelect(null);

        $this->sut->render($element);
    }
}
