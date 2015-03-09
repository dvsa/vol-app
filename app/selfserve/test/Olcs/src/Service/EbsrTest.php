<?php


namespace OlcsTest\Service;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Service\Ebsr;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

/**
 * Class EbsrTest
 * @package OlcsTest\Service
 */
class EbsrTest extends TestCase
{
    public function testCreateService()
    {
        $mockValidationChain = m::mock('Zend\InputFilter\Input');
        $mockDataService = m::mock('Olcs\Service\Data\EbsrPack');
        $mockFileService = m::mock('Common\Service\File\FileUploaderInterface');
        $mockFileService->shouldReceive('getUploader')->andReturnSelf();

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\InputFilter\EbsrPackInput')->andReturn($mockValidationChain);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\EbsrPack')->andReturn($mockDataService);
        $mockSl->shouldReceive('get')->with('FileUploader')->andReturn($mockFileService);

        $sut = new Ebsr();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Ebsr', $service);
        $this->assertSame($mockValidationChain, $service->getValidationChain());
        $this->assertSame($mockDataService, $service->getDataService());
        $this->assertSame($mockFileService, $service->getFileService());
    }

    public function testProcessPackUpload()
    {
        vfsStream::setup('tmp');
        file_put_contents(vfsStream::url('tmp/pack.zip'), 'test');

        $mockValidator = m::mock('Zend\InputFilter\Input');
        $mockValidator->shouldReceive('setValue')->with(vfsStream::url('tmp/pack.zip'));
        $mockValidator->shouldReceive('isValid')->andReturn(true);

        $packResult = ['valid' => 1, 'errors' => 1, 'messages' => ['pack2' => ['Validation failed']]];
        $mockRestClient = m::mock('Olcs\Service\Data\EbsrPack');
        $mockRestClient->shouldReceive('sendPackList')->andReturn($packResult);

        $mockFile = m::mock('Common\Service\File\File');
        $mockFile->shouldReceive('getPath')->andReturn('ebsr/dfkghdfkgjh');

        $mockFileService = m::mock('Common\Service\File\FileUploaderInterface');
        $mockFileService->shouldReceive('setFile');
        $mockFileService->shouldReceive('upload')->with('ebsr')->andReturn($mockFile);

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $sut = new Ebsr();
        $sut->setValidationChain($mockValidator);
        $sut->setDataService($mockRestClient);
        $sut->setFileService($mockFileService);

        $result = $sut->processPackUpload($data, m::type('string'));
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(
            '2  packs successfully submitted for processing<br />' .
            '1 pack validated successfully<br />1  pack contained errors',
            $result['success']
        );
        $this->assertEquals(['pack2: Validation failed'], $result['errors']);
    }

    public function testProcessPackException()
    {
        vfsStream::setup('tmp');
        file_put_contents(vfsStream::url('tmp/pack.zip'), 'test');

        $mockValidator = m::mock('Zend\InputFilter\Input');
        $mockValidator->shouldReceive('setValue')->with(vfsStream::url('tmp/pack.zip'));
        $mockValidator->shouldReceive('isValid')->andReturn(true);

        $mockRestClient = m::mock('Olcs\Service\Data\EbsrPack');
        $mockRestClient->shouldReceive('sendPackList')->andThrow(new \RuntimeException('Error uploading packs'));

        $mockFile = m::mock('Common\Service\File\File');
        $mockFile->shouldReceive('getPath')->andReturn('ebsr/dfkghdfkgjh');

        $mockFileService = m::mock('Common\Service\File\FileUploaderInterface');
        $mockFileService->shouldReceive('setFile');
        $mockFileService->shouldReceive('upload')->with('ebsr')->andReturn($mockFile);

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $sut = new Ebsr();
        $sut->setValidationChain($mockValidator);
        $sut->setDataService($mockRestClient);
        $sut->setFileService($mockFileService);

        $result = $sut->processPackUpload($data, m::type('string'));
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(['Error uploading packs'], $result['errors']);
    }

    public function testProcessPackUploadNoPacks()
    {
        vfsStream::setup('tmp');

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $sut = new Ebsr();

        $this->assertEquals(
            ['errors' => ['No packs were found in your upload, please verify your file and try again']],
            $sut->processPackUpload($data, m::type('string'))
        );
    }
}
