<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\InputFilter;

use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Nr\Filter;
use Dvsa\Olcs\Api\Service\Nr\InputFilter\ComplianceEpisodeInputFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * Class ComplianceEpisodeInputFactoryTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @covers \Dvsa\Olcs\Api\Service\Nr\InputFilter\ComplianceEpisodeInputFactory
 */
class ComplianceEpisodeInputFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $mockVrmFilter = m::mock(Filter\Vrm::class);
        $mockLicenceNumberFilter = m::mock(Filter\LicenceNumber::class);
        $mockMemberStateFilter = m::mock(Filter\Format\MemberStateCode::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with(Filter\Vrm::class)->andReturn($mockVrmFilter);
        $mockSl->shouldReceive('get')->with(Filter\LicenceNumber::class)->andReturn($mockLicenceNumberFilter);
        $mockSl->shouldReceive('get')->with(Filter\Format\MemberStateCode::class)->andReturn($mockMemberStateFilter);

        $sut = new ComplianceEpisodeInputFactory();

        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(3, $service->getFilterChain());
    }
}
