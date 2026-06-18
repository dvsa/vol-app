<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\ConvictionsPenalties;
use Common\FormService\Form\Lva\PsvDiscs;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Psv Discs Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PsvDiscsTest extends AbstractLvaFormServiceTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    protected $classToTest = PsvDiscs::class;

    protected $formName = 'Lva\PsvDiscs';

    #[\Override]
    protected function setUp(): void
    {
        $this->authService = m::mock(AuthorizationService::class);
        $this->classArgs = [$this->authService];
        parent::setUp();
    }
}
