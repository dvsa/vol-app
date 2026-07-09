<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\Application;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ApplicationTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $formHelper = m::mock(FormHelperService::class);
        $authService = m::mock(AuthorizationService::class);
        $this->sut = new Application($formHelper, $authService);
    }

    /**
     * No op
     */
    public function testAlterForm(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $this->assertNull($this->sut->alterForm($form));
    }
}
