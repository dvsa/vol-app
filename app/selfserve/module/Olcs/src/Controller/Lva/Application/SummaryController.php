<?php

/**
 * External Application Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Controller\Lva\AbstractSummaryController;

/**
 * External Application Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends AbstractSummaryController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
}
