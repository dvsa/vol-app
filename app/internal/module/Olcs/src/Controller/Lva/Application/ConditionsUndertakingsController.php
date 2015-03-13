<?php

/**
 * Internal Application Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractConditionsUndertakingsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    protected function getRenderVariables()
    {
        return array('title' => null);
    }
}
