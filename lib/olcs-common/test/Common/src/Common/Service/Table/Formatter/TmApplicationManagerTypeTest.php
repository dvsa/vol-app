<?php

/**
 * TmApplicationManagerType Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\TmApplicationManagerType;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\Mvc\Application;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TmApplicationManagerType Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmApplicationManagerTypeTest extends MockeryTestCase
{
    protected $application;

    protected $urlHelper;

    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->application = m::mock(Application::class);
        $this->sut = new TmApplicationManagerType($this->application, $this->urlHelper, $this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test formatter
     *
     * @group tmApplicationManagerType
     * @dataProvider formatProvider
     */
    public function testFormat($data, $message, $status, $expected): void
    {
        $routeParams = [
            'id' => 1,
            'action' => 'edit-tm-application',
            'transportManager' => 1
        ];

        $this->application
            ->shouldReceive('getMvcEvent')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getRouteMatch')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getParam')
                            ->with('transportManager')
                            ->andReturn(1)
                            ->getMock()
                    )
                    ->getMock()
            );

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(null, $routeParams)
            ->andReturn('url');

        if ($data['action'] !== '') {
            $this->translator->shouldReceive('translate')
                ->with($message)
                ->andReturn($status)
                ->getMock();
        }

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * @return ((int|string|string[])[]|string)[][]
     *
     * @psalm-return list{list{array{id: 1, action: 'A', tmType: array{description: 'desc1'}}, 'tm_application.table.status.new', 'status new', '<a class="govuk-link" href="url">desc1 status new</a>'}, list{array{id: 1, action: 'U', tmType: array{description: 'desc2'}}, 'tm_application.table.status.updated', 'status updated', '<a class="govuk-link" href="url">desc2 status updated</a>'}, list{array{id: 1, action: 'D', tmType: array{description: 'desc3'}}, 'tm_application.table.status.removed', 'status removed', 'desc3 status removed'}, list{array{id: 1, action: '', tmType: array{description: 'desc4'}}, '', '', '<a class="govuk-link" href="url">desc4</a>'}}
     */
    public function formatProvider(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'action' => 'A',
                    'tmType' => ['description' => 'desc1']
                ],
                'tm_application.table.status.new',
                'status new',
                '<a class="govuk-link" href="url">desc1 status new</a>'
            ],
            [
                [
                    'id' => 1,
                    'action' => 'U',
                    'tmType' => ['description' => 'desc2']
                ],
                'tm_application.table.status.updated',
                'status updated',
                '<a class="govuk-link" href="url">desc2 status updated</a>'
            ],
            [
                [
                    'id' => 1,
                    'action' => 'D',
                    'tmType' => ['description' => 'desc3']
                ],
                'tm_application.table.status.removed',
                'status removed',
                'desc3 status removed'
            ],
            [
                [
                    'id' => 1,
                    'action' => '',
                    'tmType' => ['description' => 'desc4']
                ],
                '',
                '',
                '<a class="govuk-link" href="url">desc4</a>'
            ]
        ];
    }
}
