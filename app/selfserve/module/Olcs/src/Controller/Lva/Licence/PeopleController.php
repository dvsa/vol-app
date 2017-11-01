<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreatePeople;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePeople;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Dvsa\Olcs\Transfer\Query\Application\People;
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
     * Alter form for LVA
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $table = $form->get('table')->get('table')->getTable();

        $table->removeColumn('actionLinks');
    }

    /**
     * Disallow adding (uses director change variations for add instead)
     *
     * @return Response
     */
    public function addAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Disallow deleting
     *
     *
     * @return Response
     */
    public function deleteAction()
    {
        return $this->redirectToIndex();
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
        return $request->isGet() ? parent::editAction() : $this->redirectToIndex();
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
            $this->url()->fromRoute(
                'lva-' . $this->lva . '/' . $this->section,
                [$this->getIdentifierIndex() => $this->getLicenceId()]
            ) . 'add-people?' . http_build_query(['variation' => $variationId])
        );
    }

    /**
     * Handle addition of People to a director change variation
     *
     * @return array|Response|\Zend\View\Model\ViewModel
     */
    public function addPeopleAction()
    {
        $adapter = $this->getAdapter();
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        /** @var Request $request */
        $request = $this->request;
        $variationId = $request->getQuery('variation');

        $peopleResult = $this->handleQuery(People::create(['id' => $variationId]));
        $people = $peopleResult->getResult()['application-people'];

        $request = $this->getRequest();

        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-licence-addperson')
            ->getForm(
                ['canModify' => $adapter->canModify(), 'isPartnership' => $adapter->isPartnership()]
            );

        $existingPersonId = null;
        if ($people) {
            $personData = $people[0]['person'];
            $form->populateValues(['data' => $personData]);
            $existingPersonId = $personData['id'];
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            if ($form->isValid()) {
                $validData = $form->getData()['data'];

                $validData['id'] = $variationId;

                if ($existingPersonId) {
                    $validData['person'] = $existingPersonId;
                    $this->handleCommand(UpdatePeople::create($validData));
                } else {
                    $this->handleCommand(CreatePeople::create($validData));
                }

                return $this->redirect()->toRoute(
                    'lva-director_change/financial_history',
                    ['application' => $variationId]
                );
            }
        }

        return $this->render(
            'add_person_' . $adapter->getOrganisationType(),
            $form,
            ['sectionText' => 'licence_add-Person-PersonType-' . $adapter->getOrganisationType()]
        );
    }

    /**
     * @return Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRoute(
            'lva-' . $this->lva . '/' . $this->section,
            [$this->getIdentifierIndex() => $this->getLicenceId()]
        );
    }
}
