<?php

/**
 * Constrained countries list test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ConstrainedCountriesListTest extends MockeryTestCase
{
    public $sut;
    protected $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new ConstrainedCountriesList($this->translator);
    }

    public function testFormat(): void
    {
        $data = [
            'constrainedCountries' => [
                [
                    'countryDesc' => 'United Kingdom'
                ],
                [
                    'countryDesc' => 'Trinidad & Tobago'
                ],
                [
                    'countryDesc' => '"Third" country'
                ],
            ]
        ];

        $this->translator->shouldReceive('translate')->andReturnUsing(
            static fn($key) => '_TRNSLT_' . $key
        );
        $this->assertEquals(
            '_TRNSLT_United Kingdom, _TRNSLT_Trinidad &amp; Tobago, _TRNSLT_&quot;Third&quot; country',
            $this->sut->format($data, [])
        );
    }

    public function testFormatWithColumnName(): void
    {
        $columnName = 'anyName';

        $data = [
            $columnName => [
                [
                    'countryDesc' => 'United Kingdom'
                ],
                [
                    'countryDesc' => 'Trinidad & Tobago'
                ],
                [
                    'countryDesc' => '"Third" country'
                ],
            ]
        ];
        $this->translator->shouldReceive('translate')->andReturnUsing(
            static fn($key) => '_TRNSLT_' . $key
        );
        $this->assertEquals(
            '_TRNSLT_United Kingdom, _TRNSLT_Trinidad &amp; Tobago, _TRNSLT_&quot;Third&quot; country',
            $this->sut->format($data, ['name' => $columnName])
        );
    }

    public function testFormatEmptyData(): void
    {
        $data = [
            'constrainedCountries' => []
        ];
        $this->translator->shouldReceive('translate')->andReturnUsing(
            static fn($key) => '_TRNSLT_' . $key
        );
        $this->assertEquals(
            '_TRNSLT_no.constrained.countries',
            $this->sut->format($data, [])
        );
    }
}
