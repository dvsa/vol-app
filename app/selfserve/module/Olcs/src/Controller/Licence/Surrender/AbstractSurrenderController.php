<?php

namespace Olcs\Controller\Licence\Surrender;

use Zend\Form\Form;
use Common\View\Model\Section;

use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Zend\Mvc\MvcEvent;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Zend\View\Model\ViewModel;

class AbstractSurrenderController extends AbstractSelfserveController implements ToggleAwareInterface
{
    /** @var  \Common\Service\Helper\FormHelperService */
    protected $hlpForm;
    /** @var  \Common\Service\Helper\FlashMessengerHelperService */
    protected $hlpFlashMsgr;

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    public function onDispatch(MvcEvent $e)
    {
        $this->hlpForm = $this->getServiceLocator()->get('Helper\Form');
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');
        return parent::onDispatch($e);
    }

    protected function render($titleSuffix, Form $form = null, $variables = array())
    {
//        $this->attachCurrentMessages();

        if ($titleSuffix instanceof ViewModel) {
            return $titleSuffix;
        }

        $params = array_merge(
            array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form),
            $variables
        );
        if (true) {
            // query is already cached
            $dto = LicenceQry::create(['id' => $this->params('licence')]);
            $response = $this->handleQuery($dto);
            $data = $response->getResult();
            $params['startDate'] = $data['inForceDate'];
            $params['renewalDate'] = $data['expiryDate'];
            $params['status'] = $data['status']['id'];
            $params['licNo'] = $data['licNo'];
            $params['lva'] = 'licence';

            $lvaTitleSuffix = ($titleSuffix === 'people') ?
                ($titleSuffix . '.' . $data['organisation']['type']['id']) : $titleSuffix;
            $params['title'] = 'lva.section.title.' . $lvaTitleSuffix;
        }

        return $this->renderView(new Section($params));
    }

    /**
     * Render view
     *
     * @param Section $section Section
     *
     * @return ViewModel
     */
    protected function renderView($section)
    {
        $template = $this->getRequest()->isXmlHttpRequest() ? 'ajax' : 'layout';

        $base = new ViewModel();
        $base->setTemplate('layout/' . $template)
            ->setTerminal(true)
            ->addChild($section, 'content');

        return $base;
    }
}
