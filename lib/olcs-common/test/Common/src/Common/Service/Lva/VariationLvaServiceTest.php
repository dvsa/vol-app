<?php

/**
 * Variation LVA Service tests
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Printing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Lva\VariationLvaService;

/**
 * Variation LVA Service tests
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationLvaServiceTest extends MockeryTestCase
{
    private $sut;

    /** @var TranslationHelperService */
    private $translationHelper;

    /** @var GuidanceHelperService */
    private $guidanceHelper;

    /** @var UrlHelperService */
    private $urlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->guidanceHelper = m::mock(GuidanceHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);

        $this->sut = new VariationLvaService(
            $this->translationHelper,
            $this->guidanceHelper,
            $this->urlHelper
        );
    }

    public function testAddVariationMessage(): void
    {
        $licenceId = 123;

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('lva-licence/variation', ['licence' => 123, 'redirectRoute' => null])
            ->andReturn('URL');

        $this->translationHelper->shouldReceive('translateReplace')
            ->with('variation-message', ['URL'])
            ->andReturn('translated-message');

        $this->guidanceHelper->shouldReceive('append')
            ->with('translated-message')
            ->once();

        $this->sut->addVariationMessage($licenceId);
    }
}
