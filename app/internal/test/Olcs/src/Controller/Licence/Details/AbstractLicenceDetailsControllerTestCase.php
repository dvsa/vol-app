<?php

/**
 * Abstract LicenceDetails Controller TestCase
 */
namespace OlcsTest\Controller\Licence\Details;

use CommonTest\Controller\AbstractSectionControllerTestCase;
use Zend\View\Model\ViewModel;

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
}
