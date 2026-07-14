<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Common\View\Helper\SystemInfoMessages;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Stdlib\ResponseInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\View\Helper\SystemInfoMessages::class)]
final class SystemInfoMessagesTest extends MockeryTestCase
{
    /** @var m\MockInterface|QueryService */
    protected $mockQuerySrv;

    /** @var m\MockInterface|AnnotationBuilder $mockAnnotationBuilder */
    private $mockAnnotationBuilder;

    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockAnnotationBuilder = m::mock(AnnotationBuilder::class);
        $this->mockQuerySrv = m::mock(QueryService::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTest')]
    public function test($data, $expect): void
    {
        $isOk = ($data !== null);

        $mockQryCntr = m::mock(QueryContainerInterface::class);

        $this->mockAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(GetListActive::class))
            ->once()
            ->andReturn($mockQryCntr);

        $mockResp = m::mock(ResponseInterface::class);
        $mockResp->shouldReceive('getResult')
            ->withNoArgs()
            ->times((int)$isOk)
            ->andReturn($data)
            //
            ->shouldReceive('isOk')
            ->withNoArgs()
            ->andReturn($isOk);

        $this->mockQuerySrv->shouldReceive('send')
            ->once()
            ->with($mockQryCntr)
            ->andReturn($mockResp);

        $invoke = new SystemInfoMessages($this->mockAnnotationBuilder, $this->mockQuerySrv);
        $actual = $invoke(true); // __invoke

        $this->assertEquals($expect, $actual);
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<array<string>> | int)> | string | null)>>
     *
     * @psalm-return list{array{data: null, expect: null}, array{data: array{count: 2, results: list{array{description: 'unit_Desc1'}, array{description: 'unit_Desc2 &'}}}, expect: '<div class="system-messages"><div class="system-messages__wrapper"><p>unit_Desc1</p></div><div class="system-messages__wrapper"><p>unit_Desc2 &amp;</p></div></div>'}, array{data: array<never, never>, expect: null}}
     */
    public static function dataProviderTest(): \Iterator
    {
        //  no data
        yield [
            'data' => null,
            'expect' => null,
        ];
        //  has data
        yield [
            'data' => [
                'count' => 2,
                'results' => [
                    [
                        'description' => 'unit_Desc1',
                    ],
                    [
                        'description' => 'unit_Desc2 &',
                    ],
                ],
            ],
            'expect' =>
                '<div class="system-messages"><div class="system-messages__wrapper"><p>unit_Desc1</p></div>' .
                '<div class="system-messages__wrapper"><p>unit_Desc2 &amp;</p></div>' .
                '</div>',
        ];
        // no data alt
        yield [
            'data' => [],
            'expect' => null,
        ];
    }
}
