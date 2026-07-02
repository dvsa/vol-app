<?php

namespace CommonTest\Common\Service\Data;

use Common\Preference\Language as LanguagePreference;
use Common\Service\Data\RefDataServices;
use Mockery as m;

/**
 * RefDataTestCase
 */
class RefDataTestCase extends AbstractListDataServiceTestCase
{
    /** @var  RefDataServices */
    protected $refDataServices;

    /** @var LanguagePreference */
    protected $languagePreferenceService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->languagePreferenceService = m::mock(LanguagePreference::class);

        $this->refDataServices = new RefDataServices(
            $this->abstractListDataServiceServices,
            $this->languagePreferenceService
        );
    }
}
