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
     * Create (and redirect to) a director change variation for adding directors
     *
     * @return Response
     */
    public function addAction()
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
}
