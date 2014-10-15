<?php

namespace OlcsTest\Filter;

use Olcs\Filter\DecompressUploadToTmpFactory;
use Mockery as m;

/**
 * Class DecompressUploadToTmpFactoryTest
 * @package OlcsTest\Filter
 */
class DecompressUploadToTmpFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('getServiceLocator->get')->with('Config')->andReturn(['tmpDirectory' => '/tmp/']);

        $sut = new DecompressUploadToTmpFactory();

        /** @var \Olcs\Filter\DecompressUploadToTmp $service */
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('Olcs\Filter\DecompressUploadToTmp', $service);
        $this->assertEquals('/tmp/', $service->getTempRootDir());
        $this->assertInstanceOf('Zend\Filter\Decompress', $service->getDecompressFilter());
        $this->assertInstanceOf('Olcs\Filesystem\Filesystem', $service->getFileSystem());
    }
}
