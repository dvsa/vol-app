<?php

/**
 * Business Details LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Service\Lva;

use Common\Service\Data\CategoryDataService;
use Olcs\Service\Lva\BusinessDetailsLvaService;
use Mockery as m;

/**
 * Business Details LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsLvaServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function setup()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceLocatorInterface');
        $this->form = m::mock('\Zend\Form\Form');
        $this->sut = new BusinessDetailsLvaService();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testLockDetails()
    {
        $number = m::mock();
        $name = m::mock();

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('companyNumber')
                ->andReturn($number)
                ->shouldReceive('get')
                ->with('name')
                ->andReturn($name)
                ->getMock()
            );

        $this->sm->shouldReceive('get')
            ->with('Helper\Form')
            ->andReturn(
                m::mock()
                ->shouldReceive('lockElement')
                ->with($number, 'business-details.company_number.locked')
                ->shouldReceive('lockElement')
                ->with($name, 'business-details.name.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->companyNumber->company_number')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->companyNumber->submit_lookup_company')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->name')
                ->getMock()
            );

        $this->sut->lockDetails($this->form);
    }

    public function testCreateChangeTask()
    {
        $data = [
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_HEARINGS_APPEALS,
            'description' => 'Change to business details',
            'actionDate' => '2015-03-04 12:34:56',
            'createdBy' => 123,
            'lastModifiedBy' => 123,
            'licence' => 456,
            'assignedToUser' => 1,
            'assignedToTeam' => 2,
            'lastModifiedOn' => '2015-03-04 12:34:56'
        ];

        $this->sm->shouldReceive('get')
            ->with('Helper\Date')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDate')
                ->with('Y-m-d H:i:s')
                ->andReturn('2015-03-04 12:34:56')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Entity\Task')
            ->andReturn(
                m::mock()
                ->shouldReceive('save')
                ->with($data)
                ->andReturn('foo')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Processing\Task')
            ->andReturn(
                m::mock()
                ->shouldReceive('getAssignment')
                ->with(
                    [
                        'category' => CategoryDataService::CATEGORY_APPLICATION,
                        'actionDate' => '2015-03-04 12:34:56',
                        'lastModifiedOn' => '2015-03-04 12:34:56'
                    ]
                )
                ->andReturn(
                    [
                        'assignedToUser' => 1,
                        'assignedToTeam' => 2
                    ]
                )
                ->getMock()
            );

        $this->assertEquals(
            'foo',
            $this->sut->createChangeTask(
                [
                    'user' => 123,
                    'licence' => 456
                ]
            )
        );
    }

    public function testCreateSubsidiaryChangeTask()
    {
        $data = [
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_SUBSIDIARY_DIGITAL,
            'description' => 'Subsidiary company added - sub',
            'actionDate' => '2015-03-04 12:34:56',
            'createdBy' => 123,
            'lastModifiedBy' => 123,
            'licence' => 456,
            'assignedToUser' => 1,
            'assignedToTeam' => 2,
            'lastModifiedOn' => '2015-03-04 12:34:56'
        ];

        $this->sm->shouldReceive('get')
            ->with('Helper\Date')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDate')
                ->with('Y-m-d H:i:s')
                ->andReturn('2015-03-04 12:34:56')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Entity\Task')
            ->andReturn(
                m::mock()
                ->shouldReceive('save')
                ->with($data)
                ->andReturn('foo')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Processing\Task')
            ->andReturn(
                m::mock()
                ->shouldReceive('getAssignment')
                ->with(
                    [
                        'category' => CategoryDataService::CATEGORY_APPLICATION,
                        'actionDate' => '2015-03-04 12:34:56',
                        'lastModifiedOn' => '2015-03-04 12:34:56'
                    ]
                )
                ->andReturn(
                    [
                        'assignedToUser' => 1,
                        'assignedToTeam' => 2
                    ]
                )
                ->getMock()
            );

        $this->assertEquals(
            'foo',
            $this->sut->createSubsidiaryChangeTask(
                'added',
                [
                    'user' => 123,
                    'licence' => 456,
                    'name' => 'sub'
                ]
            )
        );
    }
}
