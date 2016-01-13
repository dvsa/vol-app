<?php

/**
 * Address Change Task Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessService\Service\Lva\AddressesChangeTask;
use Common\BusinessService\Response;
use Common\Service\Data\CategoryDataService;

/**
 * Address Change Task Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AddressesChangeTaskTest extends MockeryTestCase
{
    protected $sm;

    protected $bsm;

    public function setUp()
    {
        $this->bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();

        $this->sut = new AddressesChangeTask();
        $this->sut->setBusinessServiceManager($this->bsm);
    }

    public function testProcess()
    {
        $params = [
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_ADDRESS_CHANGE_DIGITAL,
            'description' => 'Address Change',
            'licence' => 123
        ];

        $this->bsm->shouldReceive('get')
            ->with('Task')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->with($params)
                ->andReturn('response')
                ->getMock()
            );

        $this->assertEquals('response', $this->sut->process(['licenceId' => 123]));
    }
}
