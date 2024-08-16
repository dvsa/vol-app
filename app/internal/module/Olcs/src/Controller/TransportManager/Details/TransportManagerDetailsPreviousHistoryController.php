<?php

namespace Olcs\Controller\TransportManager\Details;

use Common\Controller\Lva\Traits\CrudActionTrait;
use Laminas\Form\FormInterface;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

class TransportManagerDetailsPreviousHistoryController extends AbstractTransportManagerDetailsController implements
    LeftViewProvider
{
    use CrudActionTrait;

    protected $navigationId = 'transport_manager_details_previous_history';

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/transport-manager/partials/details-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $crudAction = null;
            if (isset($data['convictions'])) {
                $crudAction = $this->getCrudAction([$data['convictions']]);
            } elseif (isset($data['previousLicences'])) {
                $crudAction = $this->getCrudAction([$data['previousLicences']]);
            }

            if ($crudAction !== null) {
                return $this->handleCrudAction(
                    $crudAction,
                    ['add-previous-conviction', 'add-previous-licence'],
                    'id'
                );
            }
        }

        $this->loadScripts(['forms/crud-table-handler', 'tm-previous-history']);

        $form = $this->getPreviousHistoryForm();

        $this->placeholder()->setPlaceholder('contentTitle', 'Previous history');

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view);
    }

    protected function getPreviousHistoryForm()
    {
        $formHelper = $this->formHelper;

        /**
         * @var \Laminas\Form\FormInterface $form
        */
        $form = $formHelper->createForm('TmPreviousHistory');

        $this->transportManagerHelper
            ->alterPreviousHistoryFieldset($form->get('previousHistory'), $this->params('transportManager'));

        return $form;
    }

    /**
     * Delete previous conviction action
     */
    public function deletePreviousConvictionAction()
    {
        return $this->deleteRecordsCommand(
            \Dvsa\Olcs\Transfer\Command\PreviousConviction\DeletePreviousConviction::class
        );
    }

    /**
     * Delete previous licence action
     */
    public function deletePreviousLicenceAction()
    {
        return $this->deleteRecordsCommand(\Dvsa\Olcs\Transfer\Command\OtherLicence\DeleteOtherLicence::class);
    }

    /**
     * Add previous conviction action
     *
     * @return mixed
     */
    public function addPreviousConvictionAction()
    {
        return $this->formAction('Add', 'TmConvictionsAndPenalties');
    }

    /**
     * Edit previous conviction action
     *
     * @return mixed
     */
    public function editPreviousConvictionAction()
    {
        return $this->formAction('Edit', 'TmConvictionsAndPenalties');
    }

    /**
     * Add previous licence action
     *
     * @return mixed
     */
    public function addPreviousLicenceAction()
    {
        return $this->formAction('Add', 'TmPreviousLicences');
    }

    /**
     * Edit previous licence action
     *
     * @return mixed
     */
    public function editPreviousLicenceAction()
    {
        return $this->formAction('Edit', 'TmPreviousLicences');
    }

    /**
     * Form action
     *
     * @param  string $type
     * @param  string $formName
     * @return mixed
     */
    protected function formAction($type, $formName)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $form = $this->alterForm($this->getForm($formName), $type);

        if (!$this->getRequest()->isPost()) {
            $form = $this->populateEditForm($form, $formName);
        }

        $this->formPost($form, [$this, 'processForm']);

        if ($this->getResponse()->getContent() !== "") {
            return $this->getResponse();
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView(
            $view,
            $type . ($formName === 'TmConvictionsAndPenalties' ? ' previous conviction' : ' previous licence')
        );
    }

    /**
     * Alter form
     *
     * @param  string        $type
     * @return FormInterface
     */
    protected function alterForm(FormInterface $form, $type)
    {
        if ($type !== 'Add') {
            $this->formHelper->remove($form, 'form-actions->addAnother');
        }
        return $form;
    }

    /**
     * Populate edit form
     *
     * @param  FormInterface $form
     * @return FormInterface
     */
    protected function populateEditForm($form, $formName)
    {
        $id = $this->getFromRoute('id');

        $data = [];
        if ($formName === 'TmConvictionsAndPenalties') {
            if (is_numeric($id)) {
                $response = $this->handleQuery(
                    \Dvsa\Olcs\Transfer\Query\PreviousConviction\PreviousConviction::create(['id' => $id])
                );
                if (!$response->isOk()) {
                    throw new \RuntimeException('Error getting OtherLicence');
                }
                $data = $response->getResult();
            }
            $dataPrepared = [
                'tm-convictions-and-penalties-details' => $data
            ];
        } else {
            if (is_numeric($id)) {
                $response = $this->handleQuery(
                    \Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence::create(['id' => $id])
                );
                if (!$response->isOk()) {
                    throw new \RuntimeException('Error getting OtherLicence');
                }
                $data = $response->getResult();
            }
            $dataPrepared = [
                'tm-previous-licences-details' => $data
            ];
        }
        $form->setData($dataPrepared);
        return $form;
    }

    /**
     * Process form and redirect back to list
     *
     * @return Response
     */
    protected function processForm(array $data)
    {
        $data = $data['validData'];
        if (isset($data['tm-convictions-and-penalties-details'])) {
            $this->savePreviousConviction($data['tm-convictions-and-penalties-details']);
            $action = 'add-previous-conviction';
        } else {
            $this->saveOtherLicence($data['tm-previous-licences-details']);
            $action = 'add-previous-licence';
        }

        if ($this->isButtonPressed('addAnother')) {
            $routeParams = [
                'transportManager' => $this->fromRoute('transportManager'),
                'action' => $action
            ];
            return $this->redirect()->toRoute(null, $routeParams);
        }

        return $this->redirectToIndex();
    }

    /**
     * Save an OtherLicence
     *
     * @param array $data array keys "id", "version", "licNo", "holderName"
     *
     * @throws \RuntimeException
     */
    private function saveOtherLicence($data)
    {
        if (is_numeric($data['id'])) {
            // update
            $command = \Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateForTma::create($data);
            $this->addSuccessMessage('generic.updated.success');
        } else {
            // create
            $data['transportManagerId'] = $this->getFromRoute('transportManager');
            $command = \Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTm::create($data);
            $this->addSuccessMessage('generic.added.success');
        }

        $response = $this->handleCommand($command);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error saving OtherLicence');
        }
    }

    /**
     * Save an PreviousConviction
     *
     * @param array $data array keys "id", "version", "convictionDate", etc
     *
     * @throws \RuntimeException
     */
    private function savePreviousConviction($data)
    {
        if (is_numeric($data['id'])) {
            // update
            $command = \Dvsa\Olcs\Transfer\Command\PreviousConviction\UpdatePreviousConviction::create($data);
            $this->addSuccessMessage('generic.updated.success');
        } else {
            // create
            $data['transportManager'] = $this->getFromRoute('transportManager');
            $command = \Dvsa\Olcs\Transfer\Command\PreviousConviction\CreatePreviousConviction::create($data);
            $this->addSuccessMessage('generic.added.success');
        }

        $response = $this->handleCommand($command);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error saving PreviousConviction');
        }
    }
}
