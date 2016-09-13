<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Table\TableBuilder;
use Mockery as m;
use Olcs\FormService\Form\Lva\Addresses;
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
        $this->formHelper
            ->shouldReceive('remove')->with($mockForm, 'contact->phone-validator')->once()->andReturnSelf()
            ->shouldReceive('remove')->with($mockForm, 'contact')->once();

        //  call
        $form = $this->sut->getForm(
            [
                'typeOfLicence' => [
                    'licenceType' => 'ltyp_sn',
                ],
                'apiData' => [
                    'correspondenceCd' => [
                        'phoneContacts' => ['unit_PhoneContacts'],
                    ],
                ],
            ]
        );

        $this->assertSame($mockForm, $form);
    }
}
