<?php

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Zend\Form\Form;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits\LicenceOperatingCentresControllerTrait;

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use LicenceControllerTrait,
        LicenceOperatingCentresControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    public function indexAction()
    {
        $this->addVariationInfoMessage();

        return parent::indexAction();
    }

    /**
     * Add variation info message
     */
    protected function addVariationInfoMessage()
    {
        $params = [
            'id' => $this->getIdentifier()
        ];

        $this->addCurrentMessage(
            $this->getServiceLocator()->get('Helper\Translation')->formatTranslation(
                '%s <a href="' . $this->url()->fromRoute('create_variation', $params) . '">%s</a>',
                array(
                    'variation-application-text',
                    'variation-application-link-text'
                )
            ),
            'info'
        );
    }

    /**
     * Alter the form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    /*
    public function alterForm(Form $form)
    {
        return $form;
    }

    protected function getIdentifier()
    {
        return $this->getLicenceId();
    }
     */
}
