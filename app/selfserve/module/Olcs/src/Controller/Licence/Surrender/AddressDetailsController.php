<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateAddresses;
use Dvsa\Olcs\Transfer\Query\Licence\Addresses;

use Zend\Form\Form;

/**
 * Class AddressDetailsController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class AddressDetailsController extends AbstractSurrenderController
{
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $formData = (array)$request->getPost();
        } else {
            $response = $this->handleQuery(Addresses::create(['id' => $this->params('licence')]));
            if (!$response->isOk()) {
                return $this->notFoundAction();
            }
            $formData = Mapper\Lva\Addresses::mapFromResult($response->getResult());
        }

        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('licence-surrender-addresses')
            ->getForm()
            ->setData($formData);

        $hasProcessed = $this->hlpForm->processAddressLookupForm($form, $request);

        if (!$hasProcessed && $request->isPost()) {
            if ($form->isValid()) {
                $response = $this->save($formData);

                if ($response === true) {
                    // redirect to review your contact details page
                    return $this->redirect()->refresh();
                }

                return $this->redirect()->refresh();

            }
        }

        return $this->render('addresses', $form);
    }

    /**
     * Save form
     *
     * @param array $formData Form Data
     *
     * @return bool
     */
    protected function save(array $formData): bool
    {
        $dtoData =
            [
                'id' => $this->params('licence'),
                'partial' => false,
            ] +
            Mapper\Lva\Addresses::mapFromForm($formData);


        $response = $this->handleCommand(UpdateAddresses::create($dtoData));

        if ($response->isOk()) {
            $this->hlpFlashMsgr->addSuccessMessage('licence.surrender.contact-details-changed');
            return true;
        }

        $this->hlpFlashMsgr->addUnknownError();
        return false;
    }
}
