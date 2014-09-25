<?php

/**
 * Operating Centre Controller
 *
 * Internal - Licence section
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits;

/**
 * Operating Centre Controller
 */
class OperatingCentreController extends AbstractLicenceDetailsController
{
    use Traits\GenericEditAction;

    protected $sectionServiceName = 'OperatingCentre\\InternalLicenceAuthorisation';

    protected $bespokeSubActions = array('add');

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_operating-centres_authorisation';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'operating_centre';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->addVariationInfoMessage();

        return $this->renderSection();
    }

    /**
     * Add operating centre
     */
    public function addAction()
    {
        $this->viewTemplateName = 'licence/add-authorisation';

        return $this->renderSection();
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        if ($this->getSectionService()->getOperatingCentresCount() === 1
            && $this->getActionId()
        ) {
            $this->getSectionService('TrafficArea')->setTrafficArea(null);
        }

        return $this->delete();
    }

    /**
     * Add variation info message
     */
    protected function addVariationInfoMessage()
    {
        $this->addCurrentMessage(
            $this->getSectionService()->formatTranslation(
                '%s <a href="' . $this->url()->fromRoute('application-variation') . '">%s</a>',
                array(
                    'variation-application-text',
                    'variation-application-link-text'
                )
            ),
            'info'
        );
    }
}
