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
    public function testCanModify($isExcOrg, $isInForce, $expect): void
    {
        $data = [
            'hasInforceLicences' => $isInForce,
            'isExceptionalType' => $isExcOrg,
        ];

        $mockResp = m::mock(\Laminas\Http\Response::class);
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

    /**
     * @return bool[][]
     *
     * @psalm-return list{array{inForce: false, isExcOrg: true, expect: true}, array{inForce: true, isExcOrg: false, expect: true}, array{isExcOrg: true, inForce: true, expect: false}}
     */
    public function dpTestCanModify(): array
    {
        return [
            [
                'inForce' => false,
                'isExcOrg' => true,
                'expect' => true,
            ],
            [
                'inForce' => true,
                'isExcOrg' => false,
                'expect' => true,
            ],
            [
                'isExcOrg' => true,
                'inForce' => true,
                'expect' => false,
            ],
        ];
    }
}
