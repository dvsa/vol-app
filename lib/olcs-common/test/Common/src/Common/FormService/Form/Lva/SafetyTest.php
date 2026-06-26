<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety;
use Laminas\Form\Form;
use Mockery as m;

/**
 * Safety Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class SafetyTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = Safety::class;

    protected $formName = 'Lva\Safety';
}
