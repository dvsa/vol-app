<?php

namespace OlcsTest\Controller\Lva\Licence;

use Common\Controller\Lva\Licence\AbstractTypeOfLicenceController;
use Olcs\Controller\Lva\Licence\TypeOfLicenceController;
use Mockery as m;

/**
 * Test Licence Type Of Licence Controller
 */
class TypeOfLicenceControllerTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * Tests index details action for licence entity Non Partner
     */
    public function testCannotUpdateLicenceTypeReturnsMessageToChangeToVariation()
    {
        $this->assertInstanceOf(AbstractTypeOfLicenceController::class, new TypeOfLicenceController());
    }
}
