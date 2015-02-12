<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Traits\PublicationControllerTrait;

/**
 * Class RegisterDecisionController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class RegisterDecisionController extends PublicInquiryController implements CaseControllerInterface
{
    use PublicationControllerTrait;

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryRegisterDecision';

    protected $inlineScripts = ['shared/definition'];

    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        if (isset($data['decisions']) && count($data['decisions']) > 0) {
            $data['decisions'] = $data['decisions'][0]['id'];
            $data['fields']['decisions'] = $data['fields']['decisions'][0]['id'];
        }

        return $data;
    }

    /**
     * Overrides the parent, make sure there's nothing there shouldn't be in the optional fields
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processSave($data)
    {
        $savedData = parent::processSave($data, false);

        $formData = $data['fields'];

        //if this was an add we need the id of the new record
        if (empty($formData['id'])) {
            $formData['id'] = $savedData['id'];
        }

        //check whether we need to publish
        $post = $this->params()->fromPost();

        if (isset($post['form-actions']['publish'])) {
            $publishData['pi'] = $data['fields']['id'];
            $publishData['text2'] = $data['fields']['decisionNotes'];
            $publishData['publicationSectionConst'] = 'decisionSectionId';

            $case = $this->getCase();

            if ($case->isTm()) {
                $publishData['case'] = $case;
                $this->getPublicationHelper()->publishTm(
                    $publishData,
                    $formData['trafficAreas'],
                    $formData['pubType'],
                    'TmHearingPublicationFilter'
                );
            } else {
                $this->getPublicationHelper()->publish(
                    $publishData,
                    'DecisionPublicationFilter'
                );
            }
        }

        return $this->redirectToIndex();
    }

    /**
     * Creates or updates a record using a data service
     *
     * @param array $data
     * @param string $service
     * @param string $filter
     * @return int
     */
    private function publish($data, $service, $filter)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get($service);
        $publicationLink = $service->createWithData($data);

        return $service->createFromObject($publicationLink, $filter);
    }

    /**
     * Get form name
     *
     * @return string
     */
    protected function getFormName()
    {
        $formName = parent::getFormName();

        $cases = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Cases');
        $caseInfo = $cases->fetchCaseData($this->getQueryOrRouteParam('case'));

        if ($caseInfo->isTm()) {
            $formName = 'PublicInquiryRegisterTmDecision';
        }

        return $formName;
    }
}
