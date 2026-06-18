<?php

declare(strict_types=1);

namespace CommonTest\Service\Helper;

use Common\Form\Form;
use Common\Service\Data\AddressDataService;
use Common\Service\Helper\AddressHelperService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Element\DateTimeSelect;
use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\Http\Request;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Session\Container;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;
use Laminas\View\Renderer\RendererInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Select;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @covers \Common\Service\Helper\FormHelperService
 */
class FormHelperServiceTest extends MockeryTestCase
{
    /** @var FormHelperService */
    private $sut;

    /** @var AuthorizationService | m\MockInterface */
    private $mockAuthSrv;

    /** @var AnnotationBuilder | m\MockInterface */
    private $mockBuilder;

    /** @var  AddressHelperService | m\MockInterface */
    private $mockHlpAddr;

    /** @var  DateHelperService | m\MockInterface */
    private $mockHlpDate;

    /** @var  RendererInterface | m\MockInterface */
    private $mockRenderer;

    /** @var  TranslationHelperService | m\MockInterface */
    private $mockTransSrv;

    /** @var  AddressDataService| m\MockInterface */
    private $mockDataAddress;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockBuilder = new AnnotationBuilder();
        $this->mockAuthSrv = m::mock(AuthorizationService::class);

        $this->mockTransSrv = m::mock(TranslationHelperService::class);
        $this->mockRenderer = m::mock(RendererInterface::class);
        $this->mockHlpAddr = m::mock(AddressHelperService::class);
        $this->mockHlpDate = m::mock(DateHelperService::class);

        $this->mockDataAddress = m::mock(AddressDataService::class);

        $config = [];

        $this->sut = new FormHelperService(
            $this->mockBuilder,
            $config,
            $this->mockAuthSrv,
            $this->mockRenderer,
            $this->mockDataAddress,
            $this->mockHlpAddr,
            $this->mockHlpDate,
            $this->mockTransSrv
        );
    }

    public function testAlterElementLabelWithAppend(): void
    {
        self::expectNotToPerformAssertions();

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('getLabel')->andReturn('My label');
        $element->shouldReceive('setLabel')->with('My labelAppended label');

        $this->sut->alterElementLabel($element, 'Appended label', 1);
    }

    public function testAlterElementLabelWithNoType(): void
    {
        self::expectNotToPerformAssertions();

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('getLabel')->andReturn('My label');
        $element->shouldReceive('setLabel')->with('Replaced label');

        $this->sut->alterElementLabel($element, 'Replaced label');
    }

    public function testAlterElementLabelWithPrepend(): void
    {
        self::expectNotToPerformAssertions();

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('getLabel')->andReturn('My label');
        $element->shouldReceive('setLabel')->with('Prepended labelMy label');

        $this->sut->alterElementLabel($element, 'Prepended label', 2);
    }

    public function testCreateFormWithInvalidForm(): void
    {
        try {
            $this->sut->createForm('NotFound');
        } catch (\RuntimeException $runtimeException) {
            $this->assertEquals('Form does not exist: NotFound', $runtimeException->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testCreateFormWithValidForm(): void
    {
        $this->markTestSkipped(
            "Laminas Annotation Builder now marked final. We're shortly moving to attributes so this whole class will
            need to change at that point. Fix this then"
        );

        //  register class in namespace; do not remove mock
        $formClass = 'Common\Form\Model\Form\MyFakeFormTest';
        m::mock($formClass);

        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm
            ->shouldReceive('add')
            ->once()
            ->with(
                [
                    'type' => \Laminas\Form\Element\Csrf::class,
                    'name' => 'security',
                    'options' => [
                        'csrf_options' => [
                            'messageTemplates' => [
                                'notSame' => 'csrf-message'
                            ],
                            'timeout' => 9999,
                        ]
                    ],
                    'attributes' => [
                        'class' => 'js-csrf-token'
                    ]
                ]
            )
            ->shouldReceive('add')
            ->once()
            ->with(
                [
                    'type' => \Laminas\Form\Element\Button::class,
                    'name' => 'form-actions[continue]',
                    'options' => [
                        'label' => 'Continue'
                    ],
                    'attributes' => [
                        'type' => 'submit',
                        'class' => 'govuk-visually-hidden',
                        'style' => 'display: none;',
                        'id' => 'hidden-continue'
                    ]
                ]
            );

        $this->mockBuilder->shouldReceive('createForm')->once()->with($formClass)->andReturn($mockForm);
        $this->mockAuthSrv->shouldReceive('isGranted')->with('internal-user')->andReturn(false);

        $config = [
            'csrf' => [
                'timeout' => 9999,
            ]
        ];

        /** @var FormHelperService | m\MockInterface $sut */
        $sut = new FormHelperService(
            $this->mockBuilder,
            $config,
            $this->mockAuthSrv,
            $this->mockRenderer,
            $this->mockDataAddress,
            $this->mockHlpAddr,
            $this->mockHlpDate,
            $this->mockTransSrv
        );

        static::assertEquals($mockForm, $sut->createForm($formClass));
    }

    public function testCreateFormWithoutCsrfAndCntn(): void
    {
        $this->markTestSkipped(
            "Laminas Annotation Builder now marked final. We're shortly moving to attributes so this whole class will
            need to change at that point. Fix this then"
        );

        //  register class in namespace; do not remove mock
        $formClass = 'Common\Form\Model\Form\MyFakeFormTest';
        m::mock($formClass);

        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldReceive('add')
            ->never()
            ->with(
                \Mockery::on(
                    static function ($arg) {
                        $res = array_intersect_key($arg, ['type' => 1, 'name' => 1]);
                        $avail = [
                            [
                                'type' => \Laminas\Form\Element\Csrf::class,
                                'name' => 'security',
                            ],
                            [
                                'type' => \Laminas\Form\Element\Button::class,
                                'name' => 'form-actions[continue]',
                            ],
                        ];
                        return in_array($res, $avail);
                    }
                )
            );

        $this->mockBuilder->shouldReceive('createForm')->once()->with($formClass)->andReturn($mockForm);
        $this->mockAuthSrv->shouldReceive('isGranted')->with('internal-user')->andReturn(false);

        static::assertEquals($mockForm, $this->sut->createForm($formClass, false, false));
    }

    public function testProcessAddressLookupWithNoPostcodeOrAddressSelected(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn([])
            ->shouldReceive('isPost')
            ->andReturn(false);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('remove')
            ->with('addresses')
            ->shouldReceive('remove')
            ->with('select');

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertFalse(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithAddressSelected(): void
    {
        $this->mockDataAddress->shouldReceive('getAddressForUprn')
            ->with(['address1'])
            ->andReturn('address_1234');

        $this->mockHlpAddr->shouldReceive('formatPostalAddress')
            ->with('address_1234')
            ->andReturn('formatted1');

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'select' => true,
                            'addresses' => ['address1']
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('remove')
            ->with('addresses')
            ->shouldReceive('remove')
            ->with('select');

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form = m::mock(\Laminas\Form\Form::class);
        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset])
            ->shouldReceive('setData')
            ->with(
                ['address' => 'formatted1']
            );

        $this->assertTrue(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessNestedAddressLookupWithAddressSelected(): void
    {
        $this->mockDataAddress->shouldReceive('getAddressForUprn')
            ->with(['address1'])
            ->andReturn('address_1234');

        $this->mockHlpAddr->shouldReceive('formatPostalAddress')
            ->with('address_1234')
            ->andReturn('formatted1');

        /** @var \Laminas\Http\Request | m\MockInterface $mockReq */
        $mockReq = m::mock(\Laminas\Http\Request::class);
        $mockReq->shouldReceive('getPost')
            ->andReturn(
                [
                    'top-level' => [
                        'address' => [
                            'searchPostcode' => [
                                'select' => true,
                                'addresses' => ['address1']
                            ]
                        ],
                        'foo' => 'bar'
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('remove')
            ->with('addresses')
            ->shouldReceive('remove')
            ->with('select');

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $topFieldset = m::mock(Fieldset::class);
        $topFieldset->shouldReceive('getName')
            ->andReturn('top-level')
            ->shouldReceive('getFieldsets')
            ->andReturn([$fieldset]);

        /** @var \Laminas\Form\FormInterface | m\MockInterface $mockForm */
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$topFieldset])
            ->shouldReceive('setData')
            ->with(
                [
                    'top-level' => [
                        'address' => 'formatted1',
                        'foo' => 'bar'
                    ]
                ]
            );

        $this->assertTrue(
            $this->sut->processAddressLookupForm($mockForm, $mockReq)
        );
    }

    public function testProcessAddressLookupWithPostcodeSearch(): void
    {
        $this->mockDataAddress->shouldReceive('getAddressesForPostcode')
            ->andReturn(['address1', 'address2']);

        $this->mockHlpAddr->shouldReceive('formatAddressesForSelect')
            ->with(['address1', 'address2'])
            ->andReturn(['formatted1', 'formatted2']);

        $form = m::mock(\Laminas\Form\Form::class);

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => 'LSX XXX'
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $addressElement = m::mock(ElementInterface::class);
        $addressElement->shouldReceive('setValueOptions')
            ->with(['formatted1', 'formatted2']);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('get')
            ->with('addresses')
            ->andReturn($addressElement);

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessNestedAddressLookupWithPostcodeSearch(): void
    {
        $this->mockDataAddress->shouldReceive('getAddressesForPostcode')
            ->andReturn(['address1', 'address2']);

        $this->mockHlpAddr->shouldReceive('formatAddressesForSelect')
            ->with(['address1', 'address2'])
            ->andReturn(['formatted1', 'formatted2']);

        $form = m::mock(\Laminas\Form\Form::class);

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'deeply' => [
                        'nested' => [
                            'address' => [
                                'searchPostcode' => [
                                    'search' => true,
                                    'postcode' => 'LSX XXX'
                                ]
                            ],
                            'foo' => 'bar'
                        ],
                        'baz' => true
                    ],
                    'test' => false
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $addressElement = m::mock(ElementInterface::class);
        $addressElement->shouldReceive('setValueOptions')
            ->with(['formatted1', 'formatted2']);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('get')
            ->with('addresses')
            ->andReturn($addressElement);

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $topFieldset = m::mock(Fieldset::class);
        $topFieldset->shouldReceive('getName')
            ->andReturn('deeply')
            ->shouldReceive('getFieldsets')
            ->andReturn(
                [
                    m::mock(Fieldset::class)
                        ->shouldReceive('getName')
                        ->andReturn('nested')
                        ->shouldReceive('getFieldsets')
                        ->andReturn([$fieldset])
                        ->getMock()
                ]
            );

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$topFieldset]);

        $this->assertTrue(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithEmptyAddresses(): void
    {
        $this->mockDataAddress->shouldReceive('getAddressesForPostcode')
            ->andReturn([]);

        $form = m::mock(\Laminas\Form\Form::class);

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => 'LSX XXX'
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $addressElement = m::mock(ElementInterface::class);
        $addressElement->shouldReceive('setValueOptions')
            ->with(['formatted1', 'formatted2']);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('remove')
            ->with('addresses')
            ->getMock()
            ->shouldReceive('remove')
            ->with('select')
            ->getMock()
            ->shouldReceive('setMessages')
            ->with(['postcode.error.no-addresses-found']);

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithEmptyPostcodeSearch(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => ''
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('remove')
            ->with('addresses')
            ->getMock()
            ->shouldReceive('remove')
            ->with('select')
            ->getMock()
            ->shouldReceive('setMessages')
            ->with(['Please enter a postcode']);

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupServiceUnavailable(): void
    {
        $this->mockDataAddress->shouldReceive('getAddressesForPostcode')
            ->andThrow(new \Exception('fail'));

        $form = m::mock(\Laminas\Form\Form::class);

        $request = m::mock(\Laminas\Http\Request::class);
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => 'LSX XXX'
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('remove')
            ->with('addresses')
            ->getMock()
            ->shouldReceive('remove')
            ->with('select')
            ->getMock()
            ->shouldReceive('setMessages')
            ->once()
            ->with(['postcode.error.not-available']);

        $fieldset = m::mock(\Common\Form\Elements\Types\Address::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $this->sut->processAddressLookupForm($form, $request)
        );
    }

    public function testDisableElementWithNestedSelector(): void
    {
        self::expectNotToPerformAssertions();

        $form = m::mock(\Laminas\Form\Form::class);

        $validator = m::mock(ElementInterface::class);
        $validator->shouldReceive('setAllowEmpty')
            ->with(true)
            ->shouldReceive('setRequired')
            ->with(false);

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('get')
            ->with('bar')
            ->andReturn($validator);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('setAttribute')
            ->with('disabled', 'disabled');

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->getMock()
            ->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $this->sut->disableElement($form, 'foo->bar');
    }

    public function testDisableElementWithDateInput(): void
    {
        $validator = m::mock(ElementInterface::class);
        $validator->shouldReceive('setAllowEmpty')
            ->with(true)
            ->shouldReceive('setRequired')
            ->with(false);

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('get')
            ->with('bar')
            ->andReturn($validator);

        $element = m::mock(DateSelect::class);

        $subElement = m::mock(Select::class);
        $subElement->shouldReceive('setAttribute')
            ->times(3)
            ->with('disabled', 'disabled');

        $element->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $form = m::mock(\Laminas\Form\Form::class);
        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->getMock()
            ->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $this->sut->disableElement($form, 'bar');
    }

    public function testDisableDateElement(): void
    {
        $element = m::mock(DateSelect::class);

        $subElement = m::mock(Select::class);
        $subElement->shouldReceive('setAttribute')
            ->times(3)
            ->with('disabled', 'disabled');

        $element->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement);

        $this->sut->disableDateElement($element);
    }

    public function testEnableDateTimeElement(): void
    {
        $element = m::mock(DateTimeSelect::class);

        $subElement = m::mock(Select::class);
        $subElement->shouldReceive('removeAttribute')
            ->times(5)
            ->with('disabled');

        $element->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->once()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->once()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement)
            ->once()
            ->shouldReceive('getHourElement')
            ->andReturn($subElement)
            ->once()
            ->shouldReceive('getMinuteElement')
            ->andReturn($subElement)
            ->once()
            ->getMock();

        $this->sut->enableDateTimeElement($element);
    }

    public function testRemove(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter);

        $this->assertEquals(
            $this->sut,
            $this->sut->remove($form, 'foo->bar')
        );
    }

    public function testDisableElements(): void
    {
        $subElement = m::mock(Select::class);
        $subElement->shouldReceive('setAttribute')
            ->times(3)
            ->with('disabled', 'disabled');

        $dateElement = m::mock(DateSelect::class);
        $dateElement->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement);

        $element = m::mock(\Laminas\Form\Element::class);
        $element->shouldReceive('setAttribute')
            ->with('disabled', 'disabled');

        $form = m::mock(\Laminas\Form\Form::class);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('getElements')
            ->andReturn([$dateElement])
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([]);

        $form->shouldReceive('getElements')
            ->andReturn([$element])
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([$fieldset]);

        $this->sut->disableElements($form);
    }

    public function testDisableValidation(): void
    {
        self::expectNotToPerformAssertions();

        $input = m::mock(Input::class);
        $input->shouldReceive('setAllowEmpty')
            ->with(true)
            ->getMock()
            ->shouldReceive('setRequired')
            ->with(false)
            ->getMock()
            ->shouldReceive('setValidatorChain');

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('getInputs')
            ->andReturn([$input]);

        $this->sut->disableValidation($filter);
    }

    public function testDisableEmptyValidation(): void
    {
        $input = m::mock(Input::class);
        $input->shouldReceive('setAllowEmpty')
            ->with(true)
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('setRequired')
            ->with(false)
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('setValidatorChain');

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturn($input)
            ->getMock()
            ->shouldReceive('has')
            ->andReturn(true)
            ->once()
            ->shouldReceive('get')
            ->with('fieldset')
            ->andReturnSelf();

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('getValue')
            ->andReturn('');

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('fieldset')
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('getElements')
            ->andReturn([]);

        $form = m::mock(\Laminas\Form\Form::class);
        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->getMock()
            ->shouldReceive('getElements')
            ->andReturn(['foo' => $element])
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([$fieldset]);

        $this->sut->disableEmptyValidation($form);
    }

    public function testDisableEmptyValidationOnElement(): void
    {
        self::expectNotToPerformAssertions();

        $input = m::mock(Input::class);
        $input->shouldReceive('setAllowEmpty')
            ->with(true)
            ->andReturnSelf()
            ->shouldReceive('setRequired')
            ->with(false)
            ->andReturnSelf()
            ->shouldReceive('setValidatorChain');

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturn($input)
            ->shouldReceive('get')
            ->with('fieldset')
            ->andReturnSelf();

        $element = m::mock(ElementInterface::class);

        $fieldset = m::mock(Fieldset::class);
        $fieldset
            ->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $form = m::mock(\Laminas\Form\Form::class);
        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->shouldReceive('get')
            ->with('fieldset')
            ->andReturn($fieldset);

        $this->sut->disableEmptyValidationOnElement($form, 'fieldset->foo');
    }

    public function testPopulateFormTable(): void
    {
        self::expectNotToPerformAssertions();

        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getRows')
            ->andReturn([1, 2, 3, 4]);

        $tableInput = m::mock(ElementInterface::class);
        $tableInput->shouldReceive('setTable')
            ->with($table, 'fieldset');

        $rowInput = m::mock(ElementInterface::class);
        $rowInput->shouldReceive('setValue')
            ->with(4);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('get')
            ->with('table')
            ->andReturn($tableInput)
            ->getMock()
            ->shouldReceive('get')
            ->with('rows')
            ->andReturn($rowInput);

        $this->sut->populateFormTable($fieldset, $table, 'fieldset');
    }

    public function testLockElement(): void
    {
        self::expectNotToPerformAssertions();

        $this->mockTransSrv
            ->shouldReceive('translate')->with('message')->andReturn('translated')
            ->shouldReceive('translate')->with('label')->andReturn('label');

        $this->mockRenderer->shouldReceive('render')
            ->andReturn('template');

        $element = m::mock(\Laminas\Form\Element::class);
        $element->shouldReceive('getLabel')
            ->andReturn('label')
            ->getMock()
            ->shouldReceive('setLabel')
            ->with('labeltemplate')
            ->getMock()
            ->shouldReceive('setLabelOption')
            ->with('disable_html_escape', true)
            ->getMock()
            ->shouldReceive('getLabelAttributes')
            ->andReturn(['foo' => 'bar'])
            ->getMock()
            ->shouldReceive('setLabelAttributes')
            ->with(
                [
                    'foo' => 'bar',
                    'class' => ''
                ]
            );

        $this->sut->lockElement($element, 'message');
    }

    public function testRemoveFieldList(): void
    {
        self::expectNotToPerformAssertions();
        $form = m::mock(\Laminas\Form\Form::class);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $filter = m::mock(InputFilter::class);
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter);

        $this->sut->removeFieldList($form, 'foo', ['bar']);
    }

    /**
     * @dataProvider companyProfileProvider
     */
    public function testProcessCompanyLookupValidData($companyProfile, $expected): void
    {
        self::expectNotToPerformAssertions();

        $form = m::mock(\Laminas\Form\Form::class);

        $data = [
            'results' => [
                $companyProfile
            ]
        ];
        $nameElement = m::mock(ElementInterface::class)->shouldReceive('setValue')
            ->with($expected['name'])
            ->getMock();

        $fieldset = m::mock(ElementInterface::class)->shouldReceive('get')
            ->with('name')
            ->andReturn($nameElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);

        $addressFieldset = m::mock(ElementInterface::class);
        foreach ($expected['address'] as $key => $value) {
            $addressFieldset->shouldReceive('get')
                ->with($key)
                ->andReturn(
                    m::mock(ElementInterface::class)->shouldReceive('setValue')->with($value)->getMock()
                );
        }

        $form->shouldReceive('get')
            ->with('registeredAddress')
            ->andReturn($addressFieldset);

        $this->sut->processCompanyNumberLookupForm($form, $data, 'data', 'registeredAddress');
    }

    /**
     * @return (string|string[])[][][]
     *
     * @psalm-return array{'full profile data': array{companyProfile: array{company_name: 'Acme Ltd', registered_office_address: array{postal_code: 'CF10 1NS', locality: 'CARDIFF', address_line_1: 'MILLENNIUM STADIUM', address_line_2: 'WESTGATE STREET', address_line_3: 'In a town', address_line_4: 'Somewhere'}}, expected: array{name: 'Acme Ltd', address: array{postcode: 'CF10 1NS', town: 'CARDIFF', addressLine1: 'MILLENNIUM STADIUM', addressLine2: 'WESTGATE STREET', addressLine3: 'In a town', addressLine4: 'Somewhere'}}}, 'missing company name': array{companyProfile: array{registered_office_address: array{postal_code: 'CF10 1NS', locality: 'CARDIFF', address_line_1: 'MILLENNIUM STADIUM', address_line_2: 'WESTGATE STREET', address_line_3: 'In a town', address_line_4: 'Somewhere'}}, expected: array{name: '', address: array{postcode: 'CF10 1NS', town: 'CARDIFF', addressLine1: 'MILLENNIUM STADIUM', addressLine2: 'WESTGATE STREET', addressLine3: 'In a town', addressLine4: 'Somewhere'}}}, 'partial address': array{companyProfile: array{company_name: 'Acme Ltd', registered_office_address: array{postal_code: 'CF10 1NS', locality: 'CARDIFF', address_line_1: 'MILLENNIUM STADIUM'}}, expected: array{name: 'Acme Ltd', address: array{postcode: 'CF10 1NS', town: 'CARDIFF', addressLine1: 'MILLENNIUM STADIUM', addressLine2: '', addressLine3: '', addressLine4: ''}}}, 'missing address': array{companyProfile: array{company_name: 'Acme Ltd'}, expected: array{name: 'Acme Ltd', address: array<never, never>}}}
     */
    public function companyProfileProvider(): array
    {
        return [
            'full profile data' => [
                'companyProfile' => [
                    'company_name' => 'Acme Ltd',
                    'registered_office_address' => [
                        'postal_code' => 'CF10 1NS',
                        'locality' => 'CARDIFF',
                        'address_line_1' => 'MILLENNIUM STADIUM',
                        'address_line_2' => 'WESTGATE STREET',
                        'address_line_3' => 'In a town',
                        'address_line_4' => 'Somewhere',
                    ]
                ],
                'expected' => [
                    'name' => 'Acme Ltd',
                    'address' => [
                        'postcode' => 'CF10 1NS',
                        'town' => 'CARDIFF',
                        'addressLine1' => 'MILLENNIUM STADIUM',
                        'addressLine2' => 'WESTGATE STREET',
                        'addressLine3' => 'In a town',
                        'addressLine4' => 'Somewhere',
                    ]
                ]
            ],
            'missing company name' => [
                'companyProfile' => [
                    'registered_office_address' => [
                        'postal_code' => 'CF10 1NS',
                        'locality' => 'CARDIFF',
                        'address_line_1' => 'MILLENNIUM STADIUM',
                        'address_line_2' => 'WESTGATE STREET',
                        'address_line_3' => 'In a town',
                        'address_line_4' => 'Somewhere',
                    ]
                ],
                'expected' => [
                    'name' => '',
                    'address' => [
                        'postcode' => 'CF10 1NS',
                        'town' => 'CARDIFF',
                        'addressLine1' => 'MILLENNIUM STADIUM',
                        'addressLine2' => 'WESTGATE STREET',
                        'addressLine3' => 'In a town',
                        'addressLine4' => 'Somewhere',
                    ]
                ]
            ],
            'partial address' => [
                'companyProfile' => [
                    'company_name' => 'Acme Ltd',
                    'registered_office_address' => [
                        'postal_code' => 'CF10 1NS',
                        'locality' => 'CARDIFF',
                        'address_line_1' => 'MILLENNIUM STADIUM',
                    ]
                ],
                'expected' => [
                    'name' => 'Acme Ltd',
                    'address' => [
                        'postcode' => 'CF10 1NS',
                        'town' => 'CARDIFF',
                        'addressLine1' => 'MILLENNIUM STADIUM',
                        'addressLine2' => '',
                        'addressLine3' => '',
                        'addressLine4' => '',
                    ]
                ]
            ],
            'missing address' => [
                'companyProfile' => [
                    'company_name' => 'Acme Ltd',
                ],
                'expected' => [
                    'name' => 'Acme Ltd',
                    'address' => [
                    ]
                ]
            ],
        ];
    }

    public function testProcessCompanyLookupInvalidData(): void
    {
        self::expectNotToPerformAssertions();
        $form = $this->createMockFormForCompanyErrors('company_number.search_no_results.error', 'data');
        $this->sut->processCompanyNumberLookupForm($form, [], 'data', 'registeredAddress');
    }

    public function testSetCompanyNotFoundError(): void
    {
        self::expectNotToPerformAssertions();
        $form = $this->createMockFormForCompanyErrors('company_number.search_no_results.error', 'data');
        $this->sut->setCompanyNotFoundError($form, 'data');
    }

    public function testSetInvalidCompanyNumberErrors(): void
    {
        self::expectNotToPerformAssertions();
        $form = $this->createMockFormForCompanyErrors('company_number.length.validation.error', 'data');
        $this->sut->setInvalidCompanyNumberErrors($form, 'data');
    }

    protected function createMockFormForCompanyErrors(string $message, string $fieldset): \Laminas\Form\Form
    {
        $form = m::mock(\Common\Form\Form::class);
        $translated = $message . '_TRANSLATED';

        $this->mockTransSrv
            ->shouldReceive('translate')->with($message)->andReturn($translated);

        $companyNumberElement = m::mock(ElementInterface::class)->shouldReceive('setMessages')
            ->with([
                'company_number' => [
                    $translated
                ]
            ])
            ->getMock();

        $fieldset = m::mock(ElementInterface::class)->shouldReceive('get')
            ->with('companyNumber')
            ->andReturn($companyNumberElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);

        return $form;
    }

    public function testSetFormActionFromRequestWhenFormHasAction(): void
    {
        $form = m::mock(Form::class)
            ->shouldReceive('hasAttribute')
            ->with('action')
            ->andReturn(true)
            ->getMock();

        $request = m::mock(Request::class)
            ->shouldReceive('getUri')->never()
            ->getMock();

        $this->sut->setFormActionFromRequest($form, $request);
    }

    public function testSetFormActionFromRequest(): void
    {
        self::expectNotToPerformAssertions();

        $form = m::mock(Form::class)
            ->shouldReceive('hasAttribute')
            ->with('action')
            ->andReturn(false)
            ->shouldReceive('setAttribute')
            ->with('action', 'URI?QUERY')
            ->getMock();

        $request = m::mock(Request::class);

        $request->shouldReceive('getUri->getPath')
            ->andReturn('URI');

        $request->shouldReceive('getUri->getQuery')
            ->andReturn('QUERY');

        $this->sut->setFormActionFromRequest($form, $request);
    }

    public function testSetFormActionFromRequestWithNoQuery(): void
    {
        self::expectNotToPerformAssertions();

        $form = m::mock(Form::class)
            ->shouldReceive('getAttribute')
            ->with('method')
            ->andReturn('POST')
            ->shouldReceive('hasAttribute')
            ->with('action')
            ->andReturn(false)
            ->shouldReceive('setAttribute')
            ->with('action', 'URI/ ')
            ->getMock();

        $request = m::mock(Request::class);

        $request->shouldReceive('getUri->getPath')
            ->andReturn('URI/');

        $request->shouldReceive('getUri->getQuery')
            ->andReturn('');

        $this->sut->setFormActionFromRequest($form, $request);
    }

    public function testRemoveOptionWithoutOption(): void
    {
        self::expectNotToPerformAssertions();

        $index = 'blap';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options);

        $this->sut->removeOption($element, $index);
    }

    public function testRemoveOptionWithOption(): void
    {
        self::expectNotToPerformAssertions();

        $index = 'foo';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options)
            ->shouldReceive('setValueOptions')
            ->with(['bar' => 'baz']);

        $this->sut->removeOption($element, $index);
    }

    public function testSetCurrentOptionWithoutCurrentOption(): void
    {
        self::expectNotToPerformAssertions();

        $index = 'blap';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options);

        $this->sut->setCurrentOption($element, $index);
    }

    public function testSetCurrentOptionWithCurrentOption(): void
    {
        self::expectNotToPerformAssertions();

        $this->mockTransSrv
            ->shouldReceive('translate')->with('current.option.suffix')->andReturn('(current)')
            ->shouldReceive('translate')->with('baz')->andReturn('baz-translated');

        $index = 'bar';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options)
            ->shouldReceive('setValueOptions')
            ->with(['foo' => 'bar', 'bar' => 'baz-translated (current)']);

        $this->sut->setCurrentOption($element, $index);
    }

    public function testSetCurrentOptionWithArrayOption(): void
    {
        self::expectNotToPerformAssertions();

        $this->mockTransSrv
            ->shouldReceive('translate')->with('current.option.suffix')->andReturn('(current)')
            ->shouldReceive('translate')->with('foo')->andReturn('foo-translated');

        $index = 'index_a';

        $options = [
            'index_a' => ['label' => 'foo'],
            'index_b' => 'bar',
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options)
            ->shouldReceive('setValueOptions')
            ->with([
                'index_a' => [
                    'label' => 'foo-translated (current)'
                ],
                'index_b' => 'bar'
            ]);

        $this->sut->setCurrentOption($element, $index);
    }

    public function testCreateFormWithRequest(): void
    {
        $sut = m::mock(FormHelperService::class)->makePartial();

        $form = m::mock(Form::class);

        $sut->shouldReceive('createForm')
            ->with('MyForm')
            ->andReturn($form)
            ->shouldReceive('setFormActionFromRequest')
            ->with($form, 'request');

        $this->assertEquals(
            $form,
            $sut->createFormWithRequest('MyForm', 'request')
        );
    }

    public function testGetValidator(): void
    {
        $validatorName = \Laminas\Validator\GreaterThan::class;

        $form = m::mock(\Laminas\Form\Form::class);
        $validator = m::mock($validatorName);
        $element = m::mock(ElementInterface::class);
        $filter = m::mock(InputFilter::class);

        $form->shouldReceive('getInputFilter')->andReturn($filter);

        $filter->shouldReceive('get')->with('myelement')->andReturn($element);

        $element->shouldReceive('getValidatorChain')->andReturn(
            m::mock(ValidatorChain::class)
                ->shouldReceive('getValidators')
                ->andReturn(
                    [
                        ['instance' => $validator],
                        ['instance' => m::mock()],
                    ]
                )
                ->getMock()
        );

        $result = $this->sut->getValidator($form, 'myelement', $validatorName);

        $this->assertSame($validator, $result);
    }

    public function testGetValidatorNotFoundReturnsNull(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);
        $element = m::mock(ElementInterface::class);
        $filter = m::mock(InputFilter::class);

        $form->shouldReceive('getInputFilter')->andReturn($filter);

        $filter->shouldReceive('get')->with('myelement')->andReturn($element);

        $element->shouldReceive('getValidatorChain')->andReturn(
            m::mock(ValidatorChain::class)
                ->shouldReceive('getValidators')
                ->andReturn([])
                ->getMock()
        );

        $this->assertNull($this->sut->getValidator($form, 'myelement', 'MyValidator'));
    }

    public function testAttachValidator(): void
    {
        /** @var FormInterface|\Mockery\MockInterface $mockForm */
        $mockForm = m::mock(FormInterface::class);
        $mockInputFilter = m::mock(InputFilterInterface::class);
        $mockValidator = m::mock(ValidatorInterface::class);
        $mockValidatorChain = m::mock(ValidatorChain::class);

        $mockForm->shouldReceive('getInputFilter')
            ->once()
            ->andReturn($mockInputFilter)
            ->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturnSelf();

        $mockInputFilter->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturnSelf()
            ->shouldReceive('getValidatorChain')
            ->andReturn($mockValidatorChain);

        $mockValidatorChain->shouldReceive('attach')
            ->once()
            ->with($mockValidator);

        $this->sut->attachValidator($mockForm, 'data->foo', $mockValidator);
    }

    public function testSetDefaultDate(): void
    {
        // mocks
        $field = m::mock(ElementInterface::class);
        $today = m::mock('\DateTime');

        // expectations
        $field->shouldReceive('getValue')->andReturn('--');
        $this->mockHlpDate->shouldReceive('getDateObject')->andReturn($today);
        $field->shouldReceive('setValue')->with($today);

        $this->assertEquals($field, $this->sut->setDefaultDate($field));
    }

    public function testSetDefaultDateFieldAlreadyHasValue(): void
    {
        // mocks
        $field = m::mock(ElementInterface::class);

        // expectations
        $field->shouldReceive('getValue')->andReturn('2015-04-09');
        $field->shouldReceive('setValue')->never();

        $this->assertEquals($field, $this->sut->setDefaultDate($field));
    }

    public function testSaveFormState(): void
    {
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldReceive('getName')->with()->once()->andReturn('FORM_NAME');

        $this->sut->saveFormState($mockForm, ['foo' => 'bar']);

        $sessionContainer = new Container('form_state');
        $this->assertEquals(['foo' => 'bar'], $sessionContainer->offsetGet('FORM_NAME'));
    }

    public function testRestoreFormState(): void
    {
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldReceive('getName')->with()->twice()->andReturn('FORM_NAME');

        $sessionContainer = new Container('form_state');
        $sessionContainer->offsetSet('FORM_NAME', ['an' => 'array']);
        $mockForm->shouldReceive('setData')->with(['an' => 'array'])->once();

        $this->sut->restoreFormState($mockForm);
    }

    public function testRemoveValueOption(): void
    {
        $options = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C'
        ];

        /** @var Select $select */
        $select = m::mock(Select::class)->makePartial();
        $select->setValueOptions($options);

        $this->sut->removeValueOption($select, 'a');

        $this->assertEquals(['b' => 'B', 'c' => 'C'], $select->getValueOptions());
    }
}
