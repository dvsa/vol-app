<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\EbsrPack;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

/**
 * Class EbsrPackTest
 * @package OlcsTest\Service\Data
 */
class EbsrPackTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $mockValidationChain = m::mock('Zend\InputFilter\Input');

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\InputFilter\EbsrPackInput')->andReturn($mockValidationChain);

        $sut = new EbsrPack();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\EbsrPack', $service);
        $this->assertSame($mockValidationChain, $service->getValidationChain());
    }

    public function testProcessPackUpload()
    {
        vfsStream::setup('tmp');
        file_put_contents(vfsStream::url('tmp/pack.zip'), 'test');

        $mockValidator = m::mock('Zend\InputFilter\Input');
        $mockValidator->shouldReceive('setValue')->with(vfsStream::url('tmp/pack.zip'));
        $mockValidator->shouldReceive('isValid')->andReturn(true);

        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('post')->andReturn(false);

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $sut = new EbsrPack();
        $sut->setValidationChain($mockValidator);
        $sut->setRestClient($mockRestClient);

        $this->assertEquals('Failed to submit packs for processing, please try again', $sut->processPackUpload($data));
    }

    public function testProcessPackUploadNoPacks()
    {
        vfsStream::setup('tmp');

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $sut = new EbsrPack();

        $this->assertEquals(
            'No packs were found in your upload, please verify your file and try again',
            $sut->processPackUpload($data)
        );
    }
}
