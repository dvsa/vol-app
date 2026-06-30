<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\Undertakings;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Undertakings (Declarations) Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = Undertakings::class;

    protected $formName = 'Lva\ApplicationUndertakings';
}
