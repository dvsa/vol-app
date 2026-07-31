<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TcSignature;
use Dvsa\Olcs\Api\Service\Document\Parser\ParserInterface;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;

/**
 * TC Signature test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class TcSignatureTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TcSignature();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $id, mixed $image): void
    {
        $bookmark = new TcSignature();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'id' => $id
                ]
            ]
        );

        $fileMock = $this->createPartialMock(File::class, ['getContent']);
        $fileMock->expects($this->once())
            ->method('getContent')
            ->willReturn('content');

        $fileStoreMock = $this->createMock(DocumentStoreInterface::class);
        $fileStoreMock->expects($this->once())
            ->method('read')
            ->with('/templates/Image/' . $image . '.jpg')
            ->willReturn($fileMock);

        $parserMock = $this->createPartialMock(ParserInterface::class, ['renderImage', 'replace', 'getFileExtension', 'extractTokens']);
        $parserMock->expects($this->once())
            ->method('renderImage')
            ->with('content', $bookmark::CONTAINER_WIDTH, $bookmark::CONTAINER_HEIGHT, 'jpeg')
            ->willReturn('an image');

        $bookmark->setFileStore($fileStoreMock);
        $bookmark->setParser($parserMock);

        $this->assertEquals(
            'an image',
            $bookmark->render()
        );
    }

    public static function renderDataProvider(): \Iterator
    {
        yield ['B', 'TC_SIG_NORTHEASTERN'];
        yield ['C', 'TC_SIG_NORTHWESTERN'];
        yield ['D', 'TC_SIG_WESTMIDLANDS'];
        yield ['F', 'TC_SIG_EASTERN'];
        yield ['G', 'TC_SIG_WELSH'];
        yield ['H', 'TC_SIG_WESTERN'];
        yield ['K', 'TC_SIG_SE_MET'];
        yield ['M', 'TC_SIG_SCOTTISH'];
        yield ['N', 'TC_SIG_NORTHERNIRELAND'];
    }
}
