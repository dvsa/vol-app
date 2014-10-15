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
        $mockTranslator = m::mock('Zend\I18n\TranslatorInterface');
        $mockTranslator->shouldReceive('getLocale')->andReturn('en_GB');

        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('setLanguage')->with('en_GB');

        $mockApiResolver = m::mock('\Common\Util\ResolveApi');
        $mockApiResolver
            ->shouldReceive('getClient')
            ->with('ebsr\pack')
            ->andReturn($mockRestClient);

        $mockValidationChain = m::mock('Zend\InputFilter\Input');

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('translator')->andReturn($mockTranslator);
        $mockSl->shouldReceive('get')->with('ServiceApiResolver')->andReturn($mockApiResolver);
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

        $data['fields']['file']['extracted_dir'] = vfsStream::url('tmp');

        $sut = new EbsrPack();
        $sut->setValidationChain($mockValidator);

        $this->assertEquals(1, $sut->processPackUpload($data));
    }
}
