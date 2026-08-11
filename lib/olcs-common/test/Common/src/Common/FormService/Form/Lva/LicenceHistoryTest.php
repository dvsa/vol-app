<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceHistory;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Mockery as m;

final class LicenceHistoryTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = LicenceHistory::class;

    protected $formName = 'Lva\LicenceHistory';

    #[\Override]
    protected function setUp(): void
    {
        $translator = m::mock(TranslationHelperService::class);
        $urlHelper = m::mock(UrlHelperService::class);
        $this->classArgs = [$translator, $urlHelper];
        parent::setUp();
    }
}
