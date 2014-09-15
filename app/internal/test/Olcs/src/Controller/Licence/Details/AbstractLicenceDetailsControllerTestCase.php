<?php

/**
 * Abstract LicenceDetails Controller TestCase
 */
namespace OlcsTest\Controller\Licence\Details;

use CommonTest\Controller\AbstractSectionControllerTestCase;
use Zend\View\Model\ViewModel;
use Common\Controller\Application\Application\ApplicationController;

/**
 * Abstract LicenceDetails Controller TestCase
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractLicenceDetailsControllerTestCase extends AbstractSectionControllerTestCase
{
    protected $identifierName = 'licence';

    /**
     * Get main view
     *
     * @param \CommonTest\Controller\ViewModel $view
     * @return ViewModel
     */
    protected function getMainView($view)
    {
        if ($view instanceof ViewModel) {

            $mainChildren = $view->getChildrenByCaptureTo('content');

            return $mainChildren[0];
        }

        $this->fail('Trying to get content child of a Response object instead of a ViewModel');
    }

    /**
     * Get licence data
     *
     * @param string $goodsOrPsv
     * @return array
     */
    protected function getLicenceData($goodsOrPsv = 'goods', $licenceType = 'ltyp_sn', $niFlag = 'N')
    {
        return array(
            'id' => 10,
            'version' => 1,
            'goodsOrPsv' => array(
                'id' => ($goodsOrPsv == 'goods' ? 'lcat_gv' : 'lcat_psv')
            ),
            'niFlag' => $niFlag,
            'licenceType' => array(
                'id' => $licenceType
            ),
            'organisation' => array(
                'type' => array(
                    'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                )
            )
        );
    }
}
