<?php

/**
 * External Application Financial History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Financial History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialHistoryController extends Lva\AbstractFinancialHistoryController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
