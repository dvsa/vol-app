<?php

/**
 * People LVA Service tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Service\Printing;

use Common\Service\Helper\FormHelperService;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Table\TableBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Form\Element;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;

/**
 * People LVA Service tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PeopleLvaServiceTest extends MockeryTestCase
{
    /** @var PeopleLvaService */
    private $sut;

    /** @var FormHelperService */
    private $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);

        $this->sut = new PeopleLvaService($this->formHelper);
    }

    public function testLockPersonForm(): void
    {
        $mockTitleElement = m::Mock(Element::class);

        $fieldset = m::mock(FieldsetInterface::class);
        $fieldset->shouldReceive('has')
            ->with('title')
            ->andReturn(true);
        $fieldset->shouldReceive('has')
            ->andReturn(false);
        $fieldset->shouldReceive('get')
            ->with('title')
            ->andReturn($mockTitleElement);

        /** @var Form|m\Mock $form */
        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);
        $form->shouldReceive('setAttribute')
            ->with('locked', true)
            ->once();

        $this->formHelper->shouldReceive('lockElement')
            ->with($mockTitleElement, 'people.org_t_rc.title.locked')
            ->once()
            ->shouldReceive('disableElement')
            ->with($form, 'data->title')
            ->once()
            ->shouldReceive('remove')
            ->with($form, 'form-actions->submit')
            ->once();

        $this->sut->lockPersonForm($form, 'org_t_rc');
    }

    public function testLockPartnershipForm(): void
    {
        /** @var Form|m\Mock $form */
        $form = m::mock(Form::class);

        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('removeActions')
            ->once();
        $table->shouldReceive('removeColumn')
            ->with('select')
            ->once();

        $this->sut->lockPartnershipForm($form, $table);
    }

    public function testLockOrganisationForm(): void
    {
        /** @var Form|m\Mock $form */
        $form = m::mock(Form::class);

        $table = m::mock();
        $table->shouldReceive('removeActions')
            ->once();
        $table->shouldReceive('removeColumn')
            ->with('select')->once();
        $table->shouldReceive('removeColumn')
            ->with('actionLinks')
            ->once();

        $this->sut->lockOrganisationForm($form, $table);
    }
}
