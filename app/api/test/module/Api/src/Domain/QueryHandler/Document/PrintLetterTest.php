<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\Document\PrintLetter;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service\Document\PrintLetter as PrintLetterService;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\Document\PrintLetter
 */
class PrintLetterTest extends QueryHandlerTestCase
{
    public $container;
    public const DOC_ID = 9999;

    /** @var  m\MockInterface */
    private $mockPrintLetterSrv;

    public function setUp(): void
    {
        $this->sut = new PrintLetter();

        $this->mockRepo('Document', Repository\Document::class);

        parent::setUp();

        $this->mockPrintLetterSrv = m::mock(PrintLetterService::class);
        $this->container->expects('get')
            ->with(PrintLetterService::class)
            ->andReturn($this->mockPrintLetterSrv);
    }

    /**
     * @dataProvider dpTestHandleQuery
     */
    public function testHandleQuery($params, $expect): void
    {
        $query = TransferQry\Document\PrintLetter::create(['id' => self::DOC_ID]);

        /** @var Entity\Doc\Document $mockDoc */
        $mockDoc = m::mock(Entity\Doc\Document::class);

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockDoc);

        $this->mockPrintLetterSrv
            ->shouldReceive('canEmail')->with($mockDoc)->andReturn($params['email'])
            ->shouldReceive('canPrint')->with($mockDoc)->andReturn($params['print']);

        $actual = $this->sut->handleQuery($query);

        static::assertEquals($expect, $actual);
    }

    public function dpTestHandleQuery(): array
    {
        return [
            [
                'params' => [
                    'email' => true,
                    'print' => false,
                ],
                'expect' => [
                    'flags' => [
                        TransferCmd\Document\PrintLetter::METHOD_EMAIL => true,
                        TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST => false,
                    ],
                ],
            ],
            [
                'params' => [
                    'email' => false,
                    'print' => true,
                ],
                'expect' => [
                    'flags' => [
                        TransferCmd\Document\PrintLetter::METHOD_EMAIL => false,
                        TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST => true,
                    ],
                ],
            ],

        ];
    }
}
