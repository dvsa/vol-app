<?php

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\TransportManagerName;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Table\Formatter\TransportManagerName
 */
class TransportManagerNameTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new TransportManagerName($this->urlHelper, $this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormatNoLvaLocation(): void
    {
        $data = [
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ]
        ];
        $column = [];
        $expected = 'Arthur Smith';

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatApplicationInternal(): void
    {
        $data = [
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'status' => [
                'id' => RefData::TMA_STATUS_POSTAL_APPLICATION,
                'description' => 'status description',
            ],
            'transportManager' => [
                'id' => 432
            ]
        ];
        $column = [
            'lva' => 'application',
            'internal' => true,
        ];
        $expected = '<a class="govuk-link" href="a-url">Arthur Smith</a>';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('transport-manager/details', ['transportManager' => 432], [], true)
            ->andReturn('a-url');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatApplicationExternal(): void
    {
        $data = [
            'id' => 333,
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'status' => [
                'id' => RefData::TMA_STATUS_POSTAL_APPLICATION,
                'description' => 'status description',
            ],
            'transportManager' => [
                'id' => 432
            ]
        ];
        $column = [
            'lva' => 'application',
            'internal' => false,
        ];
        $expected = '<a class="govuk-link" href="a-url">Arthur Smith</a>';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('lva-application/transport_manager_details', ['action' => null, 'child_id' => 333], [], true)
            ->andReturn('a-url');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatVariationInternal(): void
    {
        $data = [
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'status' => [
                'id' => RefData::TMA_STATUS_POSTAL_APPLICATION,
                'description' => 'status description',
            ],
            'transportManager' => [
                'id' => 432
            ],
            'action' => 'U',
        ];
        $column = [
            'lva' => 'variation',
            'internal' => true,
        ];
        $expected = 'translated <a class="govuk-link" href="a-url">Arthur Smith</a>';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('transport-manager/details', ['transportManager' => 432], [], true)
            ->andReturn('a-url');

        $this->translator->shouldReceive('translate')
            ->once()
            ->with('tm_application.table.status.updated')
            ->andReturn('translated');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatVariationInternalInvalidAction(): void
    {
        $data = [
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'status' => [
                'id' => RefData::TMA_STATUS_POSTAL_APPLICATION,
                'description' => 'status description',
            ],
            'transportManager' => [
                'id' => 432
            ],
        ];
        $column = [
            'lva' => 'variation',
            'internal' => true,
        ];
        $expected = ' <a class="govuk-link" href="a-url">Arthur Smith</a>';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('transport-manager/details', ['transportManager' => 432], [], true)
            ->andReturn('a-url');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatVariationExternal(): void
    {
        $data = [
            'id' => 333,
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'status' => [
                'id' => RefData::TMA_STATUS_POSTAL_APPLICATION,
                'description' => 'status description',
            ],
            'transportManager' => [
                'id' => 432
            ],
            'action' => 'U',
        ];
        $column = [
            'lva' => 'variation',
            'internal' => false,
        ];
        $expected = 'translated <a class="govuk-link" href="a-url">Arthur Smith</a>';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('lva-variation/transport_manager_details', ['action' => null, 'child_id' => 333], [], true)
            ->andReturn('a-url');

        $this->translator->shouldReceive('translate')
            ->once()
            ->with('tm_application.table.status.updated')
            ->andReturn('translated');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatVariationExternalNoLink(): void
    {
        $data = [
            'id' => 333,
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'status' => [
                'id' => RefData::TMA_STATUS_POSTAL_APPLICATION,
                'description' => 'status description',
            ],
            'transportManager' => [
                'id' => 432
            ],
            'action' => 'D',
        ];
        $column = [
            'lva' => 'variation',
            'internal' => false,
        ];
        $expected = 'translated Arthur Smith';

        $this->translator->shouldReceive('translate')
            ->once()
            ->with('tm_application.table.status.removed')
            ->andReturn('translated');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatLicenceInternal(): void
    {
        $data = [
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ],
            'transportManager' => [
                'id' => 432
            ],
        ];
        $column = [
            'lva' => 'licence',
            'internal' => true,
        ];
        $expected = '<a class="govuk-link" href="a-url">Arthur Smith</a>';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('transport-manager/details', ['transportManager' => 432], [], true)
            ->andReturn('a-url');

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatLicenceExternal(): void
    {
        $data = [
            'name' => [
                'forename' => 'Arthur',
                'familyName' => 'Smith',
            ]
        ];
        $column = [
            'lva' => 'licence',
            'internal' => false,
        ];
        $expected = 'Arthur Smith';

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }
}
