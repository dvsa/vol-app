<?php

namespace Common\Controller\Lva\Traits;

use Laminas\Form\Form;

/**
 * Variation Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait CommonVariationControllerTrait
{
    /**
     * Hook into the dispatch before the controller action is executed
     *
     * @return \Laminas\View\Model\ViewModel|null|\Laminas\Http\Response
     */
    protected function preDispatch()
    {
        if (!$this->isApplicationVariation() || !$this->isVariationTypeCorrect()) {
            return $this->notFoundAction();
        }

        return $this->checkForRedirect($this->getApplicationId());
    }

    /**
     * Redirect to the next section
     *
     * @param string $currentSection Current Section
     *
     * @return \Laminas\Http\Response
     */
    protected function goToNextSection($currentSection)
    {
        $sections = $this->getAccessibleSections();

        $index = array_search($currentSection, $sections, true);

        // If there is no next section, or the next section is disabled
        if (!isset($sections[$index + 1])) {
            return $this->goToOverview($this->getApplicationId());
        }
        $params = [$this->getIdentifierIndex() => $this->getApplicationId()];
        return $this->redirect()
            ->toRouteAjax('lva-variation/' . $sections[$index + 1], $params);
    }

    /**
     * Alter Table.  This overrides the AbstractFinancialHistoryController method.
     * This is the reason why i placed the label update logic into a seperate method
     * to avoid duplication of code.
     *
     * @param Form  $form Form
     * @param array $data Form Data
     *
     * @return Form
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        return $this->formServiceManager->get('lva-variation')->alterForm($form);
    }

    /**
     * Determine if the variation type is correct for this controller
     *
     * @return bool
     */
    protected function isVariationTypeCorrect()
    {
        return $this->fetchDataForLva()['variationType'] === null;
    }
}
