<?php

/**
 * Internal Licence Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Dvsa\Olcs\Transfer\Command\Licence\DeleteUpdateOptOutTmLetter;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete;

/**
 * Internal Licence Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersController extends Lva\AbstractTransportManagersController implements
    LicenceControllerInterface,
    TransportManagerControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    /**
     * Return different delete message if last TM.
     *
     * @return string The modal message key.
     */
    protected function getDeleteMessage()
    {

        if ($this->isLastTmLicence()) {
            return 'internal-delete.final-tm.confirmation.text';
        }

        return 'delete.confirmation.text';
    }

    protected function getDeleteConfirmationForm()
    {
        if ($this->isLastTmLicence()) {
            return 'InternalGenericDeleteConfirmation';
        }
    }

    protected function delete()
    {

        if (!$this->isLastTmLicence()) {
            return parent::delete();
        }

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /** @var \Common\Form\Form $form */
        $form = $formHelper->createFormWithRequest($this->getDeleteConfirmationForm(), $request);

        $form->setData((array)$request->getPost());

        if ($form->isValid()) {
            $data = $form->getData();
            $id = $this->params('child_id');

            return $this->handleCommand(DeleteUpdateOptOutTmLetter::create(
                [
                    'id' => $id,
                    'yesNo' => $data["YesNoRadio"]["yesNo"],
                ]
            ));
        }

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteConfirmationForm(), $form, $params);
    }
}
