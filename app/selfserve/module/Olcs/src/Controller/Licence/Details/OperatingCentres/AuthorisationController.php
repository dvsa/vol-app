<?php

/**
 * Authorisation Controller
 *
 * External - Licence section
 */
namespace Olcs\Controller\Licence\Details\OperatingCentres;

use Common\Controller\Traits;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    use Traits\GenericEditAction;

    protected $sectionServiceName = 'OperatingCentre\\ExternalLicenceAuthorisation';

    protected $bespokeSubActions = array('add');

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_operating-centres_authorisation';

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
            $this->getServiceLocator()->get('Helper\Translation')->formatTranslation(
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
