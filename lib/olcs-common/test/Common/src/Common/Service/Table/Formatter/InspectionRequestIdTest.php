<?php

/**
 * InspectionRequestId Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\InspectionRequestId;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * InspectionRequestId Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestIdTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $router;

    protected $request;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->router = m::mock(TreeRouteStack::class);
        $this->request = m::mock(Request::class);
        $this->sut = new InspectionRequestId($this->urlHelper, $this->router, $this->request);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test formatter
     *
     * @group inspectionRequestIdFormatter
     * @dataProvider formatProvider
     *
     * @param array $data
     * @param string $expectedRouteName
     * @param string $expectedUrlParams
     * @param string $expectedUrl
     * @param string $expectedOutput
     */
    public function testFormat(
        $data,
        $expectedRouteName,
        $expectedUrlParams,
        $expectedUrl,
        $expectedOutput
    ): void {

        // expectations
        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($expectedRouteName, $expectedUrlParams)
            ->andReturn($expectedUrl);

        $this->router
            ->shouldReceive('match')
            ->with($this->request)
            ->andReturn(
                m::mock()
                    ->shouldReceive('getMatchedRouteName')
                    ->once()
                    ->andReturn($expectedRouteName)
                    ->shouldReceive('getParams')
                    ->andReturn(['application' => 3])
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals($expectedOutput, $this->sut->format($data, []));
    }

    /**
     * @return (((false|int)[]|int|null|string)[]|string)[][]
     *
     * @psalm-return array{'licence inspection request': list{array{id: 1, licence: array{id: 2}, application: null}, 'licence/processing/inspection-request', array{action: 'edit', licence: 2, id: 1}, 'url1', '<a href="url1" class="govuk-link js-modal-ajax">1</a>'}, 'application inspection request': list{array{id: 1, licence: array{id: 2}, application: array{id: 3, isVariation: false}}, 'lva-application/processing/inspection-request', array{action: 'edit', application: 3, id: 1}, 'url2', '<a href="url2" class="govuk-link js-modal-ajax">1</a>'}}
     */
    public function formatProvider(): array
    {
        return [
            'licence inspection request' => [
                [
                    'id' => 1,
                    'licence' => ['id' => 2],
                    'application' => null,
                ],
                'licence/processing/inspection-request',
                [
                    'action' => 'edit',
                    'licence' => 2,
                    'id' => 1,
                ],
                'url1',
                '<a href="url1" class="govuk-link js-modal-ajax">1</a>'
            ],
            'application inspection request' => [
                [
                    'id' => 1,
                    'licence' => ['id' => 2],
                    'application' => ['id' => 3, 'isVariation' => false]
                ],
                'lva-application/processing/inspection-request',
                [
                    'action' => 'edit',
                    'application' => 3,
                    'id' => 1,
                ],
                'url2',
                '<a href="url2" class="govuk-link js-modal-ajax">1</a>'
            ]
        ];
    }
}
