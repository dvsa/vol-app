<?php

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * DisqualifyController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DisqualifyController extends AbstractController
{
    /**
     * index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $organisationId = (int) $this->params()->fromRoute('organisation');
        $personId = (int) $this->params()->fromRoute('person');

        if ($personId !== 0) {
            $data = $this->getPerson($personId);
        } elseif ($organisationId !== 0) {
            $data = $this->getOrganisation($organisationId);
        } else {
            throw new \RuntimeException('Must specify organisation or person');
        }
        $disqualificationId = $data['id'];
        $existingDisqualification = isset($data['isDisqualified']);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('Disqualify');
        $form->setData($data);
        $formHelper->setFormActionFromRequest($form, $request);

        // Must be ticked if no disqualification record exists
        if ($existingDisqualification === false) {
            $validator = new \Zend\Validator\Identical('Y');
            $validator->setMessage('form.disqualify.is-disqualified.validation');
            $formHelper->attachValidator($form, 'isDisqualified', $validator);
        }
        // Start date is required if isDisqualified is ticked
        if (isset($data['isDisqualified']) && $data['isDisqualified'] == 'N') {
            $form->getInputFilter()->get('startDate')->setRequired(false);
        }

        if ($request->isPost() && $form->isValid()) {
            if ($this->saveDisqualification($form->getData(), $disqualificationId, $personId, $organisationId)) {
                return $this->closeAjax();
            }
        }
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Disqualify');
    }

    /**
     * Generate the response, to return to the correct place
     *
     * @return \Zend\Http\Response
     */
    protected function closeAjax()
    {
        // if a person param is present
        if ($this->params()->fromRoute('person')) {
            if ($this->params()->fromRoute('organisation')) {
                return $this->redirect()->toRouteAjax('operator/people', [], [], true);
            }
            if ($this->params()->fromRoute('licence')) {
                return $this->redirect()->toRouteAjax('lva-licence/people', [], [], true);
            }
            if ($this->params()->fromRoute('application')) {
                return $this->redirect()->toRouteAjax('lva-application/people', [], [], true);
            }
            if ($this->params()->fromRoute('variation')) {
                return $this->redirect()->toRouteAjax(
                    'lva-variation/people',
                    ['application' => $this->params()->fromRoute('variation')]
                );
            }
        } else {
            // disqualify operator so return to operator details page
            return $this->redirect()->toRouteAjax('operator', [], [], true);
        }

        throw new \RuntimeException('Not setup to redirect back to anywhere');
    }


    /**
     * Save the disqualification
     *
     * @param array $formData           formData
     * @param int   $disqualificationId disqualificationId
     * @param int   $personId           personId
     * @param int   $organisationId     organisationId
     *
     * @return bool Success
     */
    protected function saveDisqualification(array $formData, $disqualificationId, $personId, $organisationId)
    {
        $params = [
            'isDisqualified' => $formData['isDisqualified'],
            'period' => $formData['period'],
            'startDate' => $formData['startDate'],
            'notes' => $formData['notes'],
        ];
        // if $disqualificationId is empty then must be creating
        if (empty($disqualificationId)) {
            // if $personCdId is empty then must be creating for an organisation
            if (empty($personId)) {
                $params['organisation'] = $organisationId;
            } else {
                $params['person'] = $personId;
            }
            $command = \Dvsa\Olcs\Transfer\Command\Disqualification\Create::create($params);
        } else {
            // updating an existing disqualification
            $params['id'] = $disqualificationId;
            $params['version'] = $formData['version'];
            $command = \Dvsa\Olcs\Transfer\Command\Disqualification\Update::create($params);
        }

        $response = $this->handleCommand($command);
        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('The disqualification details have been saved');
            return true;
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return false;
        }
    }

    /**
     * Get Organisation(Operator) data
     *
     * @param int $id operator(organisation) ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getOrganisation($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting organisation');
        }

        $organisation = $response->getResult();
        $disqualification = isset($organisation['disqualifications'][0]) ? $organisation['disqualifications'][0] : null;

        $data = [
            'name' => $organisation['name'],
            'id' => null,
        ];
        if ($disqualification !== null) {
            $data['isDisqualified'] = $disqualification['isDisqualified'];
            $data['startDate'] = $disqualification['startDate'];
            $data['period'] = $disqualification['period'];
            $data['notes'] = $disqualification['notes'];
            $data['id'] = $disqualification['id'];
            $data['version'] = $disqualification['version'];
        }

        return $data;
    }
    /**
     * Get Person data
     *
     * @param int $id person ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getPerson($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Person\Person::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting person');
        }

        $person = $response->getResult();
        $disqualification = isset($person['disqualifications'][0]) ?
            $person['disqualifications'][0] : null;

        $data = [
            'name' => $person['forename'] .' '. $person['familyName'],
            'id' => null,
        ];
        if ($disqualification !== null) {
            $data['isDisqualified'] = $disqualification['isDisqualified'];
            $data['startDate'] = $disqualification['startDate'];
            $data['period'] = $disqualification['period'];
            $data['notes'] = $disqualification['notes'];
            $data['id'] = $disqualification['id'];
            $data['version'] = $disqualification['version'];
        }

        return $data;
    }
}
