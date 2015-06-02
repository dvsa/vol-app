<?php

/**
 * Test Batch Processing Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CliTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Processing\CompaniesHouseEnqueueOrganisations;
use Common\Service\Entity\LicenceEntityService;

/**
 * Test Batch Processing Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseEnqueueOrganisationsTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new CompaniesHouseEnqueueOrganisations();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testProcess()
    {
        $type = 'foo';

        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);

        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->once()
            ->with('CompaniesHouseQueue', 'POST', ['type' => $type], null) // null bundle
            ->andReturn(99);

        $this->sut->process($type);
    }
}
