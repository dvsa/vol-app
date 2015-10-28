<?php


namespace OlcsTest\Service;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
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
        $mockCommandSender = m::mock(CommandSender::class);

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\InputFilter\EbsrPackInput')->andReturn($mockValidationChain);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\EbsrPack')->andReturn($mockDataService);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);

        $sut = new Ebsr();
        /** @var Ebsr $service */
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Ebsr', $service);
        $this->assertSame($mockValidationChain, $service->getValidationChain());
        $this->assertSame($mockDataService, $service->getDataService());
        $this->assertSame($mockCommandSender, $service->getCommandSender());
    }

    public function testProcessPackUpload()
    {
        $this->markTestSkipped();
        vfsStream::setup('tmp');
        file_put_contents(vfsStream::url('tmp/pack.zip'), 'test');

        $mockValidator = m::mock('Zend\InputFilter\Input');
        $mockValidator->shouldReceive('setValue')->with(vfsStream::url('tmp/pack.zip'));
        $mockValidator->shouldReceive('isValid')->andReturn(true);

        $packResult = ['valid' => 1, 'errors' => 1, 'messages' => ['2473' => ['Validation failed']]];
        $mockRestClient = m::mock('Olcs\Service\Data\EbsrPack');
        $mockRestClient->shouldReceive('sendPackList')->andReturn($packResult);

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $mockCommandSender = m::mock(CommandSender::class);
        $mockCommandSender->shouldReceive('send')
            ->with(m::type(Upload::class))
            ->andReturnUsing(
                function (Upload $command) {
                    $data = $command->getArrayCopy();

                    $expected = [
                        'content' => 'test',
                        'category' => 3,
                        'subCategory' => 36,
                        'filename' => 'pack.zip',
                        'description' => 'EBSR pack file',
                        'isExternal' => true
                    ];

                    foreach ($expected as $key => $value) {
                        $this->assertEquals($value, $data[$key]);
                    }

                    $response = m::mock();
                    $response->shouldReceive('getResult')
                        ->andReturn(
                            [
                                'id' => [
                                    'document' => 2473,
                                    'identifier' => 'pack.zip'
                                ]
                            ]
                        );

                    return $response;
                }
            );

        $sut = new Ebsr();
        $sut->setValidationChain($mockValidator);
        $sut->setDataService($mockRestClient);
        $sut->setCommandSender($mockCommandSender);

        $result = $sut->processPackUpload($data, 'ebsrt_refresh');

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(
            '2  packs successfully submitted for processing<br />' .
            '1 pack validated successfully<br />1  pack contained errors',
            $result['success']
        );
        $this->assertEquals(['pack.zip: Validation failed'], $result['errors']);
    }

    public function testProcessPackException()
    {
        $this->markTestSkipped();
        vfsStream::setup('tmp');
        file_put_contents(vfsStream::url('tmp/pack.zip'), 'test');

        $mockValidator = m::mock('Zend\InputFilter\Input');
        $mockValidator->shouldReceive('setValue')->with(vfsStream::url('tmp/pack.zip'));
        $mockValidator->shouldReceive('isValid')->andReturn(true);

        $mockRestClient = m::mock('Olcs\Service\Data\EbsrPack');
        $mockRestClient->shouldReceive('sendPackList')->andThrow(new \RuntimeException('Error uploading packs'));

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $mockCommandSender = m::mock(CommandSender::class);
        $mockCommandSender->shouldReceive('send')
            ->with(m::type(Upload::class))
            ->andReturnUsing(
                function (Upload $command) {
                    $data = $command->getArrayCopy();

                    $expected = [
                        'content' => 'test',
                        'category' => 3,
                        'subCategory' => 36,
                        'filename' => 'pack.zip',
                        'description' => 'EBSR pack file',
                        'isExternal' => true
                    ];

                    foreach ($expected as $key => $value) {
                        $this->assertEquals($value, $data[$key]);
                    }

                    $response = m::mock();
                    $response->shouldReceive('getResult')
                        ->andReturn(
                            [
                                'id' => [
                                    'document' => 2473,
                                    'identifier' => 'pack.zip'
                                ]
                            ]
                        );

                    return $response;
                }
            );

        $sut = new Ebsr();
        $sut->setValidationChain($mockValidator);
        $sut->setDataService($mockRestClient);
        $sut->setCommandSender($mockCommandSender);

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
