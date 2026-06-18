<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\Custom\Ecmt\InfoIconAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * InfoIconAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class InfoIconAdderTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $translationKey = 'translation.key';

        $fieldset = m::mock(Fieldset::class);

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with($translationKey)
            ->andReturn('translated translation key');

        $expectedMarkup = '<p class="govuk-!-margin-top-7 info-box__icon-wrapper info-box__text">' .
            '<i class="info-box__icon selfserve-important"></i>translated translation key</p>';

        $htmlAdder = m::mock(HtmlAdder::class);
        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'infoIcon', $expectedMarkup)
            ->once();

        $infoIconAdder = new InfoIconAdder($translator, $htmlAdder);

        $infoIconAdder->add($fieldset, $translationKey);
    }
}
