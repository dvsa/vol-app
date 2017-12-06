<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Interfaces\AdapterAwareInterface;
use Common\Controller\Lva\Traits\AdapterAwareTrait;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\People;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Olcs\Controller\Lva\Traits\VariationWizardPageFormActionsTrait;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Director Change Variation People Controller
 *
 * @author Richard Ward <richard.ward@bjss.com>
 */
class PeopleController extends AbstractController implements AdapterAwareInterface
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use VariationWizardPageFormActionsTrait;
    use AdapterAwareTrait;

    protected $lva = 'variation';
    protected $location = 'external';
    protected $section = 'people';

    /**
     * Get the required previous sections
     *
     * @return array required previous sections;
     */
    protected function getRequiredSections()
    {
        return [];
    }

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
        $adapter = $this->getVariationPeopleAdapter();

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
            ->getForm(['organisationType' =>  $adapter->getOrganisationType()]);

        $this->alterFormForLva($form);

        $existingPeople = [];
        $existingPersonIds = [];

        if ($people) {
            foreach ($people as $person) {
                $existingPeople[] = $person['person'];
                $existingPersonIds[] = $person['person']['id'];
            }

            $form->populateValues(['data' => $existingPeople]);
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            if ($form->isValid()) {
                $submittedPersonIds = [];
                foreach ($form->getData()['data'] as $submittedPerson) {
                    if ($submittedPerson['id']) {
                        $submittedPersonIds[] = $submittedPerson['id'];
                        $adapter->update($submittedPerson);
                    } else {
                        $adapter->create(array_merge($submittedPerson, ['id' => $variationId]));
                    }
                }

                $deletedPersonIds = array_diff($existingPersonIds, $submittedPersonIds);
                if ($deletedPersonIds) {
                    $adapter->delete($deletedPersonIds);
                }

                return $this->completeSection('people');
            }
        }

        return $this->render(
            'add_person_' . $adapter->getOrganisationType(),
            $form,
            ['sectionText' => '']
        );
    }

    /**
     * Get VariationPeopleAdapter
     *
     * @return VariationPeopleAdapter
     */
    private function getVariationPeopleAdapter()
    {
        /** @var VariationPeopleAdapter $adapter */
        $adapter = $this->getAdapter();
        return $adapter;
    }
}
