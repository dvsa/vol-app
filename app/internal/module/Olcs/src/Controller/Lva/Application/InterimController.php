<?php

/**
 * Internal Application Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\AbstractInterimController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

use Common\Service\Entity\ApplicationEntityService;

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
}
