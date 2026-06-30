<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\ValidityPeriod;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use IntlDateFormatter;
use Laminas\I18n\View\Helper\DateFormat;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Validity period formatter test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ValidityPeriodTest extends MockeryTestCase
{
    protected $translator;

    protected $viewHelperManager;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->viewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut = new ValidityPeriod($this->viewHelperManager, $this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $locale = 'cy_GB';
        $validFromTimestamp = 12345678;
        $validToTimestamp = 87654321;

        $row = [
            'validFromTimestamp' => $validFromTimestamp,
            'validToTimestamp' => $validToTimestamp,
            'year' => '2019',
        ];

        $dateFormatService = m::mock(DateFormat::class);
        $dateFormatService->shouldReceive('__invoke')
            ->with($validFromTimestamp, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, $locale)
            ->andReturn('1 Jan 2019');
        $dateFormatService->shouldReceive('__invoke')
            ->with($validToTimestamp, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, $locale)
            ->andReturn('31 Dec 2019');

        $this->translator->shouldReceive('getLocale')
            ->andReturn($locale);
        $this->translator->shouldReceive('translate')
            ->with('permits.irhp.fee-breakdown.validity-period.cell')
            ->andReturn('%s to %s');

        $this->viewHelperManager->shouldReceive('get')
            ->with('DateFormat')
            ->andReturn($dateFormatService);

        $this->assertEquals(
            '1 Jan to 31 Dec',
            $this->sut->format($row, [])
        );
    }
}
