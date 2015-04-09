<?php

/**
 * Abstract Transport Managers Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Abstract Transport Managers Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractTransportManagersControllerTest extends MockeryTestCase
{
    /**
     * @var \Olcs\Controller\Lva\AbstractTransportManagersController
     */
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\AbstractTransportManagersController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetCertificates()
    {
        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $mockTmHelper->shouldReceive('getCertificateFiles')
            ->with(null)
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->sut->getCertificates());
    }

    public function testProcessCertificateUpload()
    {
        $file = ['name' => 'foo.tx'];

        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $mockTmHelper->shouldReceive('getCertificateFileData')
            ->once()
            ->with(null, $file)
            ->andReturn(['foo' => 'bar']);

        $this->sut->shouldReceive('uploadFile')
            ->once()
            ->with($file, ['foo' => 'bar'])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->processCertificateUpload($file));
    }
}
