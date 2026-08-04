<?php

/**
 * PrinterDocumentCategory Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\PrinterDocumentCategory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PrinterDocumentCategory Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrinterDocumentCategoryTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new PrinterDocumentCategory($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test formatter
     *
     * @param array $data
     * @param string $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $params = [
            'rule' => $data['id'],
            'action' => 'editRule',
            'team' => $data['team']['id']
        ];

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with('admin-dashboard/admin-team-management', $params)
            ->once()
            ->andReturn('foo');

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<(array<string> | int | string)> | int)> | string)>>
     *
     * @psalm-return array{'with sub category': list{array{id: 1, team: array{id: 2}, subCategory: array{subCategoryName: 'bar', category: array{description: 'cake'}}}, '<a href="foo" class="govuk-link js-modal-ajax">cake / bar</a>'}, 'defsult setting': list{array{id: 1, team: array{id: 2}}, '<a href="foo" class="govuk-link js-modal-ajax">Default setting</a>'}}
     */
    public static function provider(): \Iterator
    {
        yield 'with sub category' => [
            [
                'id' => 1,
                'team' => [
                    'id' => 2
                ],
                'subCategory' => [
                    'subCategoryName' => 'bar',
                    'category' => [
                        'description' => 'cake'
                    ]
                ]
            ],
            '<a href="foo" class="govuk-link js-modal-ajax">cake / bar</a>'
        ];
        yield 'defsult setting' => [
            [
                'id' => 1,
                'team' => [
                    'id' => 2
                ],
            ],
            '<a href="foo" class="govuk-link js-modal-ajax">Default setting</a>'
        ];
    }
}
