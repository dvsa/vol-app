<?php

/**
 * Lock Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LockBusinessDetails;

/**
 * Lock Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LockBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new LockBusinessDetails();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterFormWithoutCompanyNumberOrName()
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();

        // Expectations
        $form->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturn($fieldset);

        $fieldset->shouldReceive('has')
            ->once()
            ->with('companyNumber')
            ->andReturn(false)
            ->shouldReceive('has')
            ->once()
            ->with('name')
            ->andReturn(false);

        $this->sut->alterForm($form);
    }

    public function testAlterFormWithCompanyNumberWithoutName()
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();
        $companyNumber = m::mock('\Zend\Form\Element');

        // Expectations
        $form->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturn($fieldset);

        $fieldset->shouldReceive('has')
            ->once()
            ->with('companyNumber')
            ->andReturn(true)
            ->shouldReceive('has')
            ->once()
            ->with('name')
            ->andReturn(false)
            ->shouldReceive('get')
            ->once()
            ->with('companyNumber')
            ->andReturn($companyNumber);

        $this->formHelper->shouldReceive('lockElement')
            ->once()
            ->with($companyNumber, 'business-details.company_number.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($form, 'data->companyNumber->company_number')
            ->shouldReceive('disableElement')
            ->once()
            ->with($form, 'data->companyNumber->submit_lookup_company');

        $this->sut->alterForm($form);
    }

    public function testAlterFormWithoutCompanyNumberWithName()
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();
        $name = m::mock('\Zend\Form\Element');

        // Expectations
        $form->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturn($fieldset);

        $fieldset->shouldReceive('has')
            ->once()
            ->with('companyNumber')
            ->andReturn(false)
            ->shouldReceive('has')
            ->once()
            ->with('name')
            ->andReturn(true)
            ->shouldReceive('get')
            ->once()
            ->with('name')
            ->andReturn($name);

        $this->formHelper->shouldReceive('lockElement')
            ->once()
            ->with($name, 'business-details.name.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($form, 'data->name');

        $this->sut->alterForm($form);
    }

    public function testAlterFormWithCompanyNumberAndName()
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();
        $name = m::mock('\Zend\Form\Element');
        $companyNumber = m::mock('\Zend\Form\Element');

        // Expectations
        $form->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturn($fieldset);

        $fieldset->shouldReceive('has')
            ->once()
            ->with('companyNumber')
            ->andReturn(true)
            ->shouldReceive('has')
            ->once()
            ->with('name')
            ->andReturn(true)
            ->shouldReceive('get')
            ->once()
            ->with('name')
            ->andReturn($name)
            ->shouldReceive('get')
            ->once()
            ->with('companyNumber')
            ->andReturn($companyNumber);

        $this->formHelper->shouldReceive('lockElement')
            ->once()
            ->with($companyNumber, 'business-details.company_number.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($form, 'data->companyNumber->company_number')
            ->shouldReceive('disableElement')
            ->once()
            ->with($form, 'data->companyNumber->submit_lookup_company')
            ->shouldReceive('lockElement')
            ->once()
            ->with($name, 'business-details.name.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($form, 'data->name');

        $this->sut->alterForm($form);
    }
}
