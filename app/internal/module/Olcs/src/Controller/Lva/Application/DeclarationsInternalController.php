<?php

namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
* Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeclarationsInternalController extends \Olcs\Controller\Lva\AbstractDeclarationsInternalController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
