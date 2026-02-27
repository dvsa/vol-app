<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Common;

use Dvsa\Olcs\Api\Service\Permits\Common\PermitTypeConfig;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitTypeConfigTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitTypeConfigTest extends MockeryTestCase
{
    public const RESTRICTED_COUNTRIES_QUESTION_KEY = 'restricted.countries.question.key';

    public const RESTRICTED_COUNTRY_IDS = ['ES', 'FR', 'DE'];

    private $permitTypeConfig;

    public function setUp(): void
    {
        $this->permitTypeConfig = new PermitTypeConfig(
            self::RESTRICTED_COUNTRIES_QUESTION_KEY,
            self::RESTRICTED_COUNTRY_IDS
        );

        parent::setUp();
    }

    public function testGetRestrictedCountriesQuestionKey(): void
    {
        $this->assertEquals(
            self::RESTRICTED_COUNTRIES_QUESTION_KEY,
            $this->permitTypeConfig->getRestrictedCountriesQuestionKey()
        );
    }

    public function testGetRestrictedCountryIds(): void
    {
        $this->assertEquals(
            self::RESTRICTED_COUNTRY_IDS,
            $this->permitTypeConfig->getRestrictedCountryIds()
        );
    }
}
