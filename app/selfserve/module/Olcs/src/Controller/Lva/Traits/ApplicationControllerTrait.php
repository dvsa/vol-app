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
use Olcs\Logging\Log\Logger;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\RefData;

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

    protected function checkAppStatus($applicationId)
    {
        $data = $this->getApplicationData($applicationId);
        return ($data['status']['id'] === RefData::APPLICATION_STATUS_NOT_SUBMITTED);
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
        $data = $this->getApplicationData($this->getApplicationId());

        $lvaTitleSuffix = ($titleSuffix === 'people') ?
            ($titleSuffix . '.' . $data['licence']['organisation']['type']['id']) : $titleSuffix;
        $params = array_merge(
            [
                'title' => 'lva.section.title.' . $lvaTitleSuffix,
                'form' => $form,
                'reference' => $data['licence']['licNo']  . '/' . $this->getApplicationId(),
                'status' => $data['status']['id'],
                'lva' => $this->lva
            ],
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
        $data = $this->getApplicationData($this->getApplicationId());

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

    protected function getApplicationData($applicationId)
    {
        // query is already cached
        $dto = ApplicationQry::create(['id' => $applicationId]);
        $response = $this->handleQuery($dto);
        return $response->getResult();
    }
}
