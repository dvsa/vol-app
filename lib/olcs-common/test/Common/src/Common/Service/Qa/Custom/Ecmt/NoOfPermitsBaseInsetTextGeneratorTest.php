<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBaseInsetTextGenerator;
use Common\View\Helper\CurrencyFormatter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsBaseInsetTextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsBaseInsetTextGeneratorTest extends MockeryTestCase
{
    public const FORMAT = '<div class="container">%s</div>';

    private $translator;

    private $currencyFormatter;

    private $noOfPermitsBaseInsetTextGenerator;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslationHelperService::class);

        $this->currencyFormatter = m::mock(CurrencyFormatter::class);

        $this->noOfPermitsBaseInsetTextGenerator = new NoOfPermitsBaseInsetTextGenerator(
            $this->translator,
            $this->currencyFormatter
        );
    }

    public function testGenerate(): void
    {
        $applicationFee = '10.00';
        $issueFee = '17.00';

        $options = [
            'applicationFee' => $applicationFee,
            'issueFee' => $issueFee
        ];

        $this->translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.inset.base')
            ->andReturn('Formatted base text. Application fee %s, issue fee %s');

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($applicationFee)
            ->andReturn('£10');
        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($issueFee)
            ->andReturn('£17');

        $expected = '<div class="container">Formatted base text. Application fee £10, issue fee £17</div>';

        $this->assertEquals(
            $expected,
            $this->noOfPermitsBaseInsetTextGenerator->generate($options, self::FORMAT)
        );
    }

    public function testGenerateIssueFeeNotApplicable(): void
    {
        $options = [
            'applicationFee' => '10.00',
            'issueFee' => 'N/A'
        ];

        $expected = '';

        $this->assertEquals(
            $expected,
            $this->noOfPermitsBaseInsetTextGenerator->generate($options, self::FORMAT)
        );
    }
}
