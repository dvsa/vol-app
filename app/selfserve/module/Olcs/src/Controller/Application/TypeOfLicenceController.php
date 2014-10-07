<?php

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractApplicationController
{
    /**
     * Type of licence section
     */
    public function indexAction()
    {
        return new Section();
    }
}
