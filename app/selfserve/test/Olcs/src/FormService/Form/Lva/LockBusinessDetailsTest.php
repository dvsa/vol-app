<?php

declare(strict_types=1);

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

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new LockBusinessDetails($this->formHelper);
    }

    public function testAlterFormWithoutCompanyNumberOrName(): void
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

    public function testAlterFormWithCompanyNumberWithoutName(): void
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();
        $companyNumber = m::mock(\Laminas\Form\Element::class);

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

    public function testAlterFormWithoutCompanyNumberWithName(): void
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();
        $name = m::mock(\Laminas\Form\Element::class);

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

    public function testAlterFormWithCompanyNumberAndName(): void
    {
        // Params
        $form = m::mock();
        $fieldset = m::mock();
        $name = m::mock(\Laminas\Form\Element::class);
        $companyNumber = m::mock(\Laminas\Form\Element::class);

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
