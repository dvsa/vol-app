<?php

/**
 * Internal Application Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractInterimController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Dvsa\Olcs\Transfer\Command\Application\UpdateInterim;

/**
 * Internal Application Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class InterimController extends AbstractInterimController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
    protected $updateInterimCommand = UpdateInterim::class;
}
