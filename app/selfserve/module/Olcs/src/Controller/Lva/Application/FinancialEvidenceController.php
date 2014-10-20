<?php

/**
 * External Application Financial Evidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Financial Evidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialEvidenceController extends Lva\AbstractFinancialEvidenceController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
