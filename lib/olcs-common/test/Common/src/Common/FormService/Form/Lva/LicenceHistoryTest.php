<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceHistory;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Mockery as m;

class LicenceHistoryTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = LicenceHistory::class;

    protected $formName = 'Lva\LicenceHistory';

    protected $translator;

    protected $urlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslationHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->classArgs = [$this->translator, $this->urlHelper];
        parent::setUp();
    }
}
