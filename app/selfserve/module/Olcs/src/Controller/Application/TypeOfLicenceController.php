<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;

/**
 * Type Of Licence Controller
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
        $form = $this->getHelperService('FormHelper')
            ->createForm('Lva\TypeOfLicence');

        return new Section(
            [
                'title' => 'Type of licence',
                'form' => $form
            ]
        );
    }
}
