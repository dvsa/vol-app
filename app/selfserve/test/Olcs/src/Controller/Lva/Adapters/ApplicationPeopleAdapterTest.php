<?php

namespace OlcsTest\Controller\Lva\Adapters;

use Common\Controller\Lva\AbstractController;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;

/**
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @covers \Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter
 */
class ApplicationPeopleAdapterTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestCanModify
     */
    public function testCanModify($isExcOrg, $isInForce, $expect)
    {
        $data = [
            'hasInforceLicences' => $isInForce,
            'isExceptionalType' => $isExcOrg,
        ];

        $mockResp = m::mock(\Zend\Http\Response::class);
        $mockResp
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('getResult')->andReturn($data);

        /** @var ApplicationPeopleAdapter | m\MockInterface $sut */
        $sut = m::mock(ApplicationPeopleAdapter::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('handleQuery')->andReturn($mockResp);

        $sut->loadPeopleData(AbstractController::LVA_LIC, 999);

        static::assertEquals($expect, $sut->canModify());
    }

    public function dpTestCanModify()
    {
        return [
            [
                'isExcOrg' => true,
                'inForce' => false,
                'expect' => false,
            ],
            [
                'isExcOrg' => false,
                'inForce' => false,
                'expect' => true,
            ],
            [
                'isExcOrg' => false,
                'inForce' => true,
                'expect' => false,
            ],
            [
                'isExcOrg' => true,
                'inForce' => true,
                'expect' => false,
            ],
        ];
    }
}
