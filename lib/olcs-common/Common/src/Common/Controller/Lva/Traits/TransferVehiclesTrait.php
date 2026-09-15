<?php

namespace Common\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\OtherActiveLicences;

/**
 * Transfer Vehicles Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait TransferVehiclesTrait
{
    /**
     * Transfer vehicles
     *
     * @return \Common\View\Model\Section | \Laminas\Http\Response
     */
    protected function transferVehicles()
    {
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery(OtherActiveLicences::create(['id' => $this->getLicenceId()]));

        $options = [];

        foreach ($response->getResult()['otherActiveLicences'] as $licence) {
            $options[$licence['id']] = $licence['licNo'];
        }

        $form = $this->getVehicleTransferForm($options);

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData((array) $request->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $ids = explode(',', $this->params()->fromRoute('child_id'));

                $dtoData = [
                    'id' => $this->getLicenceId(),
                    'target' => $formData['data']['licence'],
                    'licenceVehicles' => $ids
                ];

                /** @var \Common\Service\Cqrs\Response $response */
                $response = $this->handleCommand(TransferVehicles::create($dtoData));

                /** @var \Common\Service\Helper\FlashMessengerHelperService $fm */
                $fm = $this->flashMessengerHelper;

                if ($response->isOk()) {
                    $fm->addSuccessMessage('licence.vehicles_transfer.form.vehicles_transfered');

                    return $this->redirect()->toRouteAjax(
                        $this->getBaseRoute(),
                        [
                            $this->getIdentifierIndex() => $this->getIdentifier(),
                        ],
                        [
                            'query' => $request->getQuery()->toArray(),
                        ]
                    );
                }

                if ($response->isClientError()) {
                    $messages = $response->getResult()['messages'];

                    /** @var \Common\Service\Helper\TranslationHelperService $th */
                    $th = $this->translationHelper;
                    $licNo = $options[$formData['data']['licence']];

                    $knownError = false;

                    if (isset($messages['LIC_TRAN_1'])) {
                        $fm->addErrorMessage(
                            $th->translateReplace('licence.vehicles_transfer.form.message_exceed', [$licNo])
                        );

                        $knownError = true;
                    }

                    if (isset($messages['LIC_TRAN_2'])) {
                        $fm->addErrorMessage(
                            $th->translateReplace(
                                'licence.vehicles_transfer.form.message_already_on_licence_singular',
                                [
                                    implode(', ', json_decode($messages['LIC_TRAN_2'], true)),
                                    $licNo
                                ]
                            )
                        );

                        $knownError = true;
                    }

                    if (isset($messages['LIC_TRAN_3'])) {
                        $fm->addErrorMessage(
                            $th->translateReplace(
                                'licence.vehicles_transfer.form.message_already_on_licence',
                                [
                                    implode(', ', json_decode($messages['LIC_TRAN_3'], true)),
                                    $licNo
                                ]
                            )
                        );

                        $knownError = true;
                    }

                    if ($knownError == false) {
                        $fm->addCurrentErrorMessage('unknown-error');
                    } else {
                        return $this->redirect()->toRouteAjax(
                            $this->getBaseRoute(),
                            [
                                $this->getIdentifierIndex() => $this->getIdentifier(),
                            ],
                            [
                                'query' => $request->getQuery()->toArray(),
                            ]
                        );
                    }
                }

                if ($response->isServerError()) {
                    $fm->addCurrentErrorMessage('unknown-error');
                }
            }
        }

        return $this->render('transfer_vehicles', $form);
    }

    /**
     * Get vehicles transfer form
     *
     * @param array $options Options
     *
     * @return \Laminas\Form\Form
     */
    protected function getVehicleTransferForm($options)
    {
        $form = $this->formHelper
            ->createFormWithRequest('Lva\VehiclesTransfer', $this->getRequest());

        $form->get('data')->get('licence')->setValueOptions($options);

        return $form;
    }
}
