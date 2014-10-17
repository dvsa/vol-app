<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\View\Model\Section;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends Lva\AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait,
        Lva\Traits\ApplicationTypeOfLicenceTrait;

    protected $location = 'external';
    protected $lva = 'application';

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function renderCreateApplication($titleSuffix, Form $form = null)
    {
        return new Section(array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form));
    }
}
