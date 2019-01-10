<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateAddresses;
use Dvsa\Olcs\Transfer\Query\Licence\Addresses;

/**
 * Class AddressDetailsController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class AddressDetailsController extends AbstractSurrenderController
{
    protected $form;

    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $formData = (array)$request->getPost();
        } else {
            $formData = Mapper\Licence\Surrender\AddressDetails::mapFromResult($this->licence);
        }

        $this->form = $this->getForm('Licence\Surrender\Addresses')
            ->setData($formData);

        $hasProcessed = $this->hlpForm->processAddressLookupForm($this->form, $request);

        if (!$hasProcessed && $request->isPost()) {
            if ($this->form->isValid()) {
                $response = $this->save($formData);

                if ($response === true) {
                    return $this->redirect()->toRoute(
                        'licence/surrender/review-contact-details/GET',
                        [],
                        [],
                        true
                    );
                }

                return $this->redirect()->refresh();
            }
        }

        $params = $this->getViewVariables();

        return $this->renderView($params);
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

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => 'lva.section.title.addresses',
            'licNo' => $this->licence['licNo'],
            'form' => $this->form,
            'backLink' => $this->getBackLink('licence/surrender/review-contact-details/GET'),
        ];
    }
}
