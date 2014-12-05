<?php

namespace Olcs\Controller\Cases\PublicInquiry;

/**
 * Class RegisterDecisionController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class RegisterDecisionController extends PublicInquiryController
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryRegisterDecision';

    protected $inlineScripts = ['shared/definition'];

    /**
     * Overrides the parent, make sure there's nothing there shouldn't be in the optional fields
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processSave($data)
    {
        parent::processSave($data, false);

        //check whether we need to publish
        $post = $this->params()->fromPost();

        if (isset($post['form-actions']['publish'])) {
            $publishData['pi'] = $data['fields']['id'];
            $publishData['text2'] = $data['fields']['decisionNotes'];
            $publishData['publicationSectionConst'] = 'decisionSectionId';

            $this->publish($publishData);
        }

        return $this->redirectToIndex();
    }

    /**
     * @param array $publishData
     * @return \Common\Data\Object\Publication
     */
    private function publish($publishData)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\PublicationLink');
        $publicationLink = $service->createWithData($publishData);

        return $service->createPublicationLink($publicationLink, 'DecisionPublicationFilter');
    }
}
