<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Adapters;

use Common\Controller\Lva\AbstractController;
use Common\Service\Cqrs\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;

/**
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter::class)]
final class ApplicationPeopleAdapterTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanModify')]
    public function testCanModify(bool $isExcOrg, bool $isInForce, bool $expect): void
    {
        $data = [
            'hasInforceLicences' => $isInForce,
            'isExceptionalType' => $isExcOrg,
            'application' => [
                'licenceType' => [
                    'id' => 'lcat_exc'
                ]
            ]
        ];

        $mockResp = m::mock(Response::class);
        $mockResp
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('getResult')->andReturn($data);

        /** @var ApplicationPeopleAdapter | m\MockInterface $sut */
        $sut = m::mock(ApplicationPeopleAdapter::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('handleQuery')->andReturn($mockResp);

        $sut->loadPeopleData(AbstractController::LVA_LIC, 999);

        $this->assertEquals($expect, $sut->canModify());
    }

    /**
     * @return \Iterator<(int | string), array<bool>>
     *
     * @psalm-return list{array{inForce: false, isExcOrg: true, expect: true}, array{inForce: true, isExcOrg: false, expect: true}, array{isExcOrg: true, inForce: true, expect: false}}
     */
    public static function dpTestCanModify(): \Iterator
    {
        yield [
            'isExcOrg' => false,
            'isInForce' => true,
            'expect' => true,
        ];
        yield [
            'isExcOrg' => true,
            'isInForce' => false,
            'expect' => true,
        ];
        yield [
            'isExcOrg' => true,
            'isInForce' => true,
            'expect' => false,
        ];
    }
}
