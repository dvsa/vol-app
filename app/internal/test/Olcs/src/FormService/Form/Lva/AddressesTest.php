<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Table\TableBuilder;
use Mockery as m;
use Olcs\FormService\Form\Lva\Addresses;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Olcs\FormService\Form\Lva\Addresses
 */
class AddressesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = Addresses::class;

    /** @var  \Olcs\FormService\Form\Lva\Addresses */
    protected $sut;

    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock(\Zend\Form\Form::class);

        $this->formHelper
            ->shouldReceive('createForm')->andReturn($mockForm);

        //  check set label
        $mockElmSaveBtn = m::mock(\Zend\Form\ElementInterface::class);
        $mockElmSaveBtn->shouldReceive('setLabel')->with('internal.save.button')->once();

        $mockFsActions = m::mock(\Zend\Form\FieldsetInterface::class);
        $mockFsActions->shouldReceive('get')->once()->andReturn($mockElmSaveBtn);

        $mockForm->shouldReceive('get')->with('form-actions')->once()->andReturn($mockFsActions);

        //  check fill table
        $mockTbl = m::mock(TableBuilder::class);

        $mockTableBuilder = m::mock(TableBuilder::class)
            ->shouldReceive('prepareTable')
            ->with('lva-phone-contacts', ['unit_PhoneContacts'])
            ->andReturn($mockTbl)
            ->getMock();

        $mockSm = m::mock(ServiceLocatorInterface::class)
            ->shouldReceive('get')->with('Table')->once()->andReturn($mockTableBuilder)
            ->getMock();

        $this->fsm->shouldReceive('getServiceLocator')->once()->andReturn($mockSm);

        $mockElmPhoneContactsTbl = m::mock(\Zend\Form\Fieldset::class);
        $mockForm->shouldReceive('get')
            ->with('phoneContactsTable')
            ->once()
            ->andReturn($mockElmPhoneContactsTbl);

        $this->formHelper->shouldReceive('populateFormTable')
            ->with($mockElmPhoneContactsTbl, $mockTbl)
            ->once();

        //  check remove elements
        $mockFieldEmail = m::mock(\Zend\Form\ElementInterface::class)
            ->shouldReceive('getName')->once()->andReturn('email')
            ->getMock();
        $mockFieldOther = m::mock(\Zend\Form\ElementInterface::class)
            ->shouldReceive('getName')->once()->andReturn('unit_not_email_field')
            ->getMock();

        $mockFieldset = m::mock(\Zend\Form\FieldsetInterface::class)
            ->shouldReceive('getIterator')->once()->andReturn(
                new \ArrayIterator([$mockFieldOther, $mockFieldEmail])
            )
            ->shouldReceive('setOptions')->once()->with([])->andReturnSelf()
            ->shouldReceive('setLabel')->once()->with('')
            ->getMock();

        $mockForm
            ->shouldReceive('get')->with('contact')->once()->andReturn($mockFieldset);

        $this->formHelper
            ->shouldReceive('remove')->with($mockForm, 'contact->unit_not_email_field')->once();

        //  check change email setting
        $mockInputFilter = m::mock(InputFilter::class)
            ->shouldReceive('get')
            ->with('contact')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('email')
                    ->andReturn(
                        m::mock(\Zend\InputFilter\Input::class)
                            ->shouldReceive('setRequired')->once()->with(false)->andReturnSelf()
                            ->shouldReceive('setAllowEmpty')->once()->with(true)
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $mockForm->shouldReceive('getInputFilter')->once()->andReturn($mockInputFilter);

        //  call
        $form = $this->sut->getForm(
            [
                'typeOfLicence' => [
                    'licenceType' => 'ltyp_sn',
                ],
                'corrPhoneContacts' => [
                    'unit_PhoneContacts',
                ],
            ]
        );

        static::assertSame($mockForm, $form);
    }
}
