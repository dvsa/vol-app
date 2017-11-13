<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Interfaces\AdapterAwareInterface;
use Common\Controller\Lva\Traits\AdapterAwareTrait;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\People;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Director Change Variation People Controller
 *
 * @author Rob Caiger <richard.ward@bjss.com>
 */
class PeopleController extends AbstractController implements AdapterAwareInterface
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use AdapterAwareTrait;

    protected $lva = 'variation';
    protected $location = 'external';
    protected $section = 'people';

    /**
     * Get the variation type upon which controllers using this trait can operate
     *
     * @see RefData::VARIATION_TYPE_DIRECTOR_CHANGE for example
     *
     * @return string
     */
    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    /**
     * Get the start of the start of the wizard
     *
     * @return array
     */
    public function getStartRoute()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());
        return ['name' => 'lva-licence/people', 'params' => ['licence' => $licenceId]];
    }

    /**
     * Get the text (or translation string) for the saveAndContinue button
     *
     * @return string
     */
    public function getSubmitActionText()
    {
        return 'Continue to financial History';
    }

    /**
     * Get the route name for the next page in the wizard
     *
     * @return string
     */
    protected function getNextPageRouteName()
    {
        return 'lva-director_change/financial_history';
    }

    /**
     * Handle addition of People to a director change variation
     *
     * @return array|Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $adapter = $this->geVariationPeopleAdapter();

        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        $variationId = $this->getIdentifier();

        $peopleResult = $this->handleQuery(People::create(['id' => $variationId]));
        $people = $peopleResult->getResult()['application-people'];

        /** @var Request $request */
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

                if ($existingPersonId) {
                    $validData['id'] = $existingPersonId;
                    $adapter->update($validData);
                } else {
                    $validData['id'] = $variationId;
                    $adapter->create($validData);
                }

                return $this->completeSection('people');
            }
        }

        return $this->render(
            'add_person_' . $adapter->getOrganisationType(),
            $form,
            ['sectionText' => 'licence_add-Person-PersonType-' . $adapter->getOrganisationType()]
        );
    }

    /**
     * Get LicencePeopleAdapter
     *
     * @return VariationPeopleAdapter
     */
    private function geVariationPeopleAdapter()
    {
        /** @var VariationPeopleAdapter $adapter */
        $adapter = $this->getAdapter();
        return $adapter;
    }
}
