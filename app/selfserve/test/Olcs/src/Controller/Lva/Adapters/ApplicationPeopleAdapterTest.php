<?php

namespace OlcsTest\Controller\Lva\Adapters;

use Common\Controller\Lva\AbstractController;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * @covers \Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter
 */
class ApplicationPeopleAdapterTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestCanModify
     */
    public function testCanModify($type, $isInForce, $expect)
    {
        $data = [
            'licence' => [
                'organisation' => [
                    'hasInforceLicences' => $isInForce,
                    'type' => [
                        'id' => $type,
                    ],
                ],
            ],
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

        $sut->loadPeopleData(AbstractController::LVA_APP, 999);

        static::assertEquals($expect, $sut->canModify());
    }

    public function dpTestCanModify()
    {
        return [
            'ltd & Not InForce' => [
                'type' => OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY,
                'inForce' => false,
                'expect' => true,
            ],
            'llp & Not InForce' => [
                'type' => OrganisationEntityService::ORG_TYPE_LLP,
                'inForce' => false,
                'expect' => true,
            ],
            'other & Not InForce' => [
                'type' => OrganisationEntityService::ORG_TYPE_OTHER,
                'inForce' => false,
                'expect' => true,
            ],
            'ltd & InForce' => [
                'type' => OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY,
                'inForce' => true,
                'expect' => false,
            ],
            'SoleTraider & Not InForce' => [
                'type' => OrganisationEntityService::ORG_TYPE_SOLE_TRADER,
                'inForce' => false,
                'expect' => false,
            ],
            ' & Not InForce' => [
                'type' => OrganisationEntityService::ORG_TYPE_PARTNERSHIP,
                'inForce' => false,
                'expect' => false,
            ],
        ];
    }
}
