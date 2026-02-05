<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractController;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\People;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Olcs\Controller\Lva\Traits\VariationWizardPageFormActionsTrait;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Director Change Variation People Controller
 *
 * @author Richard Ward <richard.ward@bjss.com>
 */
class PeopleController extends AbstractController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use VariationWizardPageFormActionsTrait;

    protected $lva = 'variation';
    protected string $location = 'external';
    protected $section = 'people';
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormServiceManager $formServiceManager,
        protected VariationPeopleAdapter $lvaAdapter
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

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
     * Get the previous wizard page location
     *
     * @see consuming class to provide implementation
     *
     * @return array route definition
     */
    protected function getPreviousPageRoute()
    {
        return $this->getStartRoute();
    }

    /**
     * Provide the route for the next page in the wizard
     *
     * @return array route definition
     */
    protected function getNextPageRoute()
    {
        return [
            'name' => 'lva-director_change/financial_history',
            'params' => ['application' => $this->getIdentifier()]
        ];
    }

    /**
     * Get the text (or translation string) for the saveAndContinue button
     *
     * @return string
     */
    public function getSubmitActionText()
    {
        return 'continue.finance.history.button';
    }

    /**
     * Handle addition of People to a director change variation
     *
     * @return array|Response|\Laminas\View\Model\ViewModel
     */
    #[\Override]
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
        $form = $this->formServiceManager
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
                $submittedPerson = $form->getData()['data'];
                if ($submittedPerson['id']) {
                    $submittedPersonIds[] = $submittedPerson['id'];
                    $adapter->update($submittedPerson);
                } else {
                    $adapter->create(array_merge($submittedPerson, ['id' => $variationId]));
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
        $adapter = $this->lvaAdapter;
        return $adapter;
    }
}
