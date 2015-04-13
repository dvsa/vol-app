<?php

/**
 * External Licence Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    public function indexAction()
    {
        $data = $this->getServiceLocator()->get('Entity\Licence')->getConditionsAndUndertakings($this->getIdentifier());

        $config = $this->getServiceLocator()->get('Review\LicenceConditionsUndertakings')
            ->getConfigFromData($data);

        $this->getServiceLocator()->get('Helper\Guidance')->append('cannot-change-conditions-undertakings-guidance');

        $view = new ViewModel($config);
        $view->setTemplate('partials/read-only/subSections');

        $section = new ViewModel(['title' => 'section.name.conditions_undertakings']);
        $section->setTemplate('pages/licence-page');
        $section->addChild($view, 'content');

        return $this->renderView($section);
    }
}
