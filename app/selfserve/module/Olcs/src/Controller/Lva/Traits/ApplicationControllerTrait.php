<?php

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Lva\Traits\CommonApplicationControllerTrait;
use Common\View\Model\Section;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    use ExternalControllerTrait,
        CommonApplicationControllerTrait;

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        if ($this->isApplicationVariation()) {
            return $this->notFoundAction();
        }

        return $this->checkForRedirect($this->getApplicationId());
    }

    /**
     * Check if the user has access to the application
     *
     * @NOTE We might want to consider caching this information within the session, to save making this request on each
     *  section
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function checkAccess($applicationId)
    {
        $dto = ApplicationQry::create(['id' => $applicationId]);
        $response = $this->handleQuery($dto);
        $data = $response->getResult();

        $doesBelong = $data['licence']['organisation']['id'] == $this->getCurrentOrganisationId();

        if (!$doesBelong) {
            $this->addErrorMessage('application-no-access');
        }

        return $doesBelong;
    }

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @param array $variables
     * @return \Common\View\Model\Section
     */
    protected function render($titleSuffix, Form $form = null, $variables = array())
    {
        $this->attachCurrentMessages();

        if ($titleSuffix instanceof ViewModel) {
            return $titleSuffix;
        }

        $sectionName = $titleSuffix;
        // overrides for any instance where the section name differs from the view template name
        $sectionOverrides = [
            'person' => 'people'
        ];
        if (array_key_exists($titleSuffix, $sectionOverrides)) {
            $sectionName = $sectionOverrides[$titleSuffix];
        }

        $progress = $this->getSectionStepProgress($sectionName);

        $params = array_merge(
            array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form),
            $progress,
            $variables
        );

        $section = new Section($params);

        $template = $this->getRequest()->isXmlHttpRequest() ? 'ajax' : 'layout';

        $base = new ViewModel();
        $base->setTemplate('layout/' . $template)
            ->setTerminal(true)
            ->addChild($section, 'content');

        return $base;
    }

    /**
     * @param string $currentSection
     * @return array
     */
    protected function getSectionStepProgress($currentSection)
    {
        $applicationId = $this->getApplicationId();

        $dto = ApplicationQry::create(['id' => $applicationId]);
        $response = $this->handleQuery($dto);
        $data = $response->getResult();

        // Don't show steps on variations
        if ($data['isVariation'] == true) {
            return [];
        }

        $sectionStatus = $this->setEnabledAndCompleteFlagOnSections(
            $data['sections'],
            $data['applicationCompletion']
        );

        $sections = array_keys($sectionStatus);

        $index = array_search($currentSection, $sections);

        if ($index === false) {
            return [];
        }

        // we can pass this array straight to the view
        return ['stepX' => $index+1, 'stepY' => count($sections)];
    }

    protected function postSave($section)
    {
        $applicationId = $this->getApplicationId();

        if ($section !== 'undertakings') {
            $this->resetUndertakings($applicationId);
        }

        $this->updateCompletionStatuses($applicationId, $section);
    }

    protected function resetUndertakings($applicationId)
    {
        $this->getServiceLocator()->get('Entity\Application')
            ->forceUpdate($applicationId, ['declarationConfirmation' => 'N']);
    }
}
