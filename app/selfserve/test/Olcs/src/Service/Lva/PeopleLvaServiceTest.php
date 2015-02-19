<?php

/**
 * People LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Service\Lva;

use Common\Service\Data\CategoryDataService;
use Olcs\Service\Lva\PeopleLvaService;
use Mockery as m;
use Common\Service\Entity\OrganisationEntityService;

/**
 * People LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PeopleLvaServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function setup()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->form = m::mock('\Zend\Form\Form');
        $this->sut = new PeopleLvaService();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testLockPersonFormWithHideSubmit()
    {
        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('title')
                ->andReturn('title')
                ->shouldReceive('get')
                ->with('forename')
                ->andReturn('forename')
                ->shouldReceive('get')
                ->with('familyName')
                ->andReturn('familyName')
                ->shouldReceive('get')
                ->with('otherName')
                ->andReturn('otherName')
                ->shouldReceive('get')
                ->with('birthDate')
                ->andReturn('birthDate')
                ->getMock()
            );

        $this->sm->shouldReceive('get')
            ->with('Helper\Form')
            ->andReturn(
                m::mock()
                ->shouldReceive('lockElement')
                ->with('title', 'people.title.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->title')

                ->shouldReceive('lockElement')
                ->with('forename', 'people.forename.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->forename')

                ->shouldReceive('lockElement')
                ->with('familyName', 'people.familyName.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->familyName')

                ->shouldReceive('lockElement')
                ->with('otherName', 'people.otherName.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->otherName')

                ->shouldReceive('lockElement')
                ->with('birthDate', 'people.birthDate.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->birthDate')

                ->shouldReceive('remove')
                ->with($this->form, 'form-actions->submit')

                ->getMock()
            );

        $this->sut->lockPersonForm($this->form, true);
    }

    public function testLockOrganisationFormWithPartnership()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('getType')
            ->with(123)
            ->andReturn(
                [
                    'type' => [
                        'id' => OrganisationEntityService::ORG_TYPE_PARTNERSHIP
                    ]
                ]
            )
            ->getMock()
        );

        $table = m::mock()
            ->shouldReceive('removeActions')
            ->shouldReceive('removeColumn')
            ->with('select')
            ->getMock();

        $this->sut->lockOrganisationForm($this->form, $table, 123);
    }
}
