<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\File;

use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\File\Exception;
use Dvsa\Olcs\Api\Service\File\MimeNotAllowedException;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\DocumentShare\Service\WebDavClient as ContentStoreClient;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader::class)]
class ContentStoreFileUploaderTest extends MockeryTestCase
{
    public const IDENTIFIER = 'unit_Identifier';

    /** @var ContentStoreFileUploader */
    protected $sut;

    /** @var m\MockInterface  */
    protected $mockContentStoreCli;

    public function setUp(): void
    {
        $this->sut = new ContentStoreFileUploader();

        $this->mockContentStoreCli = m::mock(ContentStoreClient::class);

        $sm = m::mock(ServiceManager::class);

        $sm->shouldReceive('setService')
            ->andReturnUsing(
                function ($alias, $service) use ($sm) {
                    $sm->shouldReceive('get')->with($alias)->andReturn($service);
                    $sm->shouldReceive('has')->with($alias)->andReturn(true);
                    return $sm;
                }
            );

        $sm->setService('ContentStore', $this->mockContentStoreCli);
        $sm->setService('Logger', $this->createStub(\Dvsa\OlcsTest\SafeLogger::class));

        static::assertSame($this->sut, $this->sut->__invoke($sm, null));
    }

    public function testDownload(): void
    {
        $returnedFile = m::mock(DsFile::class);
        $this->mockContentStoreCli->shouldReceive('read')
            ->once()
            ->with(self::IDENTIFIER)
            ->andReturn($returnedFile);

        static::assertEquals($returnedFile, $this->sut->download(self::IDENTIFIER));
    }

    public function testRemove(): void
    {
        $this->mockContentStoreCli->shouldReceive('remove')
            ->once()
            ->with(self::IDENTIFIER)
            ->andReturn(true);

        static::assertTrue($this->sut->remove(self::IDENTIFIER));
    }

    public function testUpload(): void
    {
        $expectContent = 'unit_content';

        $file = new DsFile();
        $file->setContent($expectContent);

        $response = m::mock(Response::class);
        $response->shouldReceive('isSuccess')
            ->andReturn(true);

        $this->mockContentStoreCli->shouldReceive('write')
            ->once()
            ->with(self::IDENTIFIER, m::type(DsFile::class))
            ->andReturn($response);

        //  call & check
        $actual = $this->sut->upload(self::IDENTIFIER, $file);

        static::assertSame($file, $actual);
        static::assertEquals($expectContent, $actual->getContent());
        static::assertEquals(self::IDENTIFIER, $actual->getIdentifier());
    }

    public function testUploadFail(): void
    {
        $respBody = 'unit_RespBody';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(ContentStoreFileUploader::ERR_UNABLE_UPLOAD, $respBody, 500));

        $response = m::mock(Response::class);
        $response
            ->shouldReceive('isSuccess')->once()->andReturn(false)
            ->shouldReceive('getStatusCode')->andReturn(Response::STATUS_CODE_500)
            ->shouldReceive('getBody')->once()->andReturn($respBody);

        $this->mockContentStoreCli->shouldReceive('write')
            ->once()
            ->andReturn($response);

        $this->sut->upload(self::IDENTIFIER, new DsFile());
    }

    public function testUploadFailMime(): void
    {
        $this->expectException(MimeNotAllowedException::class);

        $response = m::mock(Response::class);
        $response
            ->shouldReceive('isSuccess')->once()->andReturn(false)
            ->shouldReceive('getStatusCode')->once()->andReturn(Response::STATUS_CODE_415);

        $this->mockContentStoreCli->shouldReceive('write')
            ->once()
            ->andReturn($response);

        $this->sut->upload(self::IDENTIFIER, new DsFile());
    }
}
