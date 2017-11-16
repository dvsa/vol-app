<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Zend\Form\Form;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * External Licence People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
    protected $section = 'people';

    /**
     * Prevent default licence actions
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
    }

    /**
     * Disallow adding (uses director change variations for add instead)
     *
     * @return Response
     */
    public function addAction()
    {
        return $this->redirectToIndexWithPermissionError();
    }

    /**
     * Disallow deleting
     *
     * @return Response
     */
    public function deleteAction()
    {
        $licencePeopleAdapter = $this->getLicencePeopleAdapter();
        $licencePeopleAdapter->loadPeopleData($this->lva, $this->getIdentifier());
        if ($licencePeopleAdapter->isExceptionalOrganisation()) {
            return $this->redirectToIndexWithPermissionError();
        }
        return parent::deleteAction();
    }

    /**
     * Disallow editing by disallowing non-get requests (still allow the edit page to be accessible via get)
     *
     * @return Response
     */
    public function editAction()
    {
        /** @var Request $request */
        $request = $this->request;
        return $request->isGet() ? parent::editAction() : $this->redirectToIndexWithPermissionError();
    }

    /**
     * Intercept the 'Add' POST action on index and create (and redirect to) the director change variation wizard
     *
     * @param array  $data             Data
     * @param array  $rowsNotRequired  Action
     * @param string $childIdParamName Child route identifier
     * @param string $route            Route
     *
     * @return Response
     */
    protected function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        if (!isset($data['action']) or $data['action'] !== 'Add') {
            return parent::handleCrudAction($data, $rowsNotRequired, $childIdParamName, $route);
        }

        return $this->redirectToIndexIfNonPost()
            ?: $this->createNewDirectorChangeVariation();
    }

    /**
     * Redirect to index page if this is not a POST request
     *
     * @return null|Response
     */
    private function redirectToIndexIfNonPost()
    {
        /** @var Request $request */
        $request = $this->request;
        return $request->isPost() ? null : $this->redirectToIndex();
    }

    /**
     * Create a new Director Change Variation and redirect to the first page of the wizard
     *
     * @return Response
     */
    private function createNewDirectorChangeVariation()
    {
        $licencePeopleAdapter = $this->getLicencePeopleAdapter();
        $licencePeopleAdapter->loadPeopleData($this->lva, $this->getIdentifier());
        if ($licencePeopleAdapter->isExceptionalOrganisation() !== false) {
            return $this->redirectToIndexWithPermissionError();
        }

        $variationResult = $this->handleCommand(
            CreateVariation::create(
                [
                    'id' => $id = $this->getLicenceId(),
                    'variationType' => RefData::VARIATION_TYPE_DIRECTOR_CHANGE
                ]
            )
        );

        $variationId = $variationResult->getResult()['id']['application'];

        return $this->redirect()->toUrl(
            $this->url()->fromRoute('lva-director_change/people', ['application' => $variationId])
        );
    }


    /**
     * Redirect to the people index and display a permission flash message
     *
     * @return Response
     */
    private function redirectToIndexWithPermissionError()
    {
        $this->addErrorMessage('cannot-perform-action');
        return $this->redirectToIndex();
    }

    /**
     * @return Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'lva-' . $this->lva . '/' . $this->section,
            [$this->getIdentifierIndex() => $this->getLicenceId()]
        );
    }

    /**
     * Get LicencePeopleAdapter
     *
     * @return LicencePeopleAdapter
     */
    private function getLicencePeopleAdapter()
    {
        /** @var LicencePeopleAdapter $adapter */
        $adapter = $this->getAdapter();
        return $adapter;
    }
}
