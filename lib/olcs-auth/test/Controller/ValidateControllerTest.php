<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\Service;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateController
 */
class ValidateControllerTest extends MockeryTestCase
{
    public function testIndexAction(): void
    {
        $returnedArray = ['response' => 'content'];
        $identityProvider = m::mock(IdentityProviderInterface::class);
        $identityProvider->expects('validateToken')->withNoArgs()->andReturn($returnedArray);

        $sut = new ValidateController($identityProvider);

        static::assertEquals($returnedArray, $sut->indexAction()->getVariables());
    }
}
