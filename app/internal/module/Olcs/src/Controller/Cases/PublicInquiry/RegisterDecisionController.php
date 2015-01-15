<?php

namespace Olcs\Controller\Cases\PublicInquiry;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * Class RegisterDecisionController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class RegisterDecisionController extends PublicInquiryController implements CaseControllerInterface
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryRegisterDecision';

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

            $this->publish(
                $publishData,
                'Common\Service\Data\PublicationLink',
                'DecisionPublicationFilter'
            );
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
}
