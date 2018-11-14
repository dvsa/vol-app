<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
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

        //  prepare form data
        if ($request->isPost()) {
            $formData = (array)$request->getPost();
        } else {
            //  get api data
            $response = $this->handleQuery(
                TransferQry\Licence\Addresses::create(['id' => $this->params('licence')])
            );

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
            if ($this->isValid($form, $formData)) {
                $response = $this->save($formData);

                if ($response !== null) {
                    if ($response === true) {
                        return $this->completeSection('addresses');
                    }

                    return $response;
                }
            }
        }

        return $this->render('addresses', $form);
    }
}
