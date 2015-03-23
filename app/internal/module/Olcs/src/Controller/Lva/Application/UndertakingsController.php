<?php

/**
 * Internal Application Undertakings Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\AbstractUndertakingsController as InternalAbstractUndertakingsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
* Internal Application Undertakings Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsController extends InternalAbstractUndertakingsController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
