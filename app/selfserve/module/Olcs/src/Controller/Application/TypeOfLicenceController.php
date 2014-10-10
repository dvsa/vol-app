<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\Controller\Traits\Lva;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractApplicationController
{
    use Lva\TypeOfLicenceTrait,
        Lva\ApplicationTypeOfLicenceTrait;

    /**
     * Render the section
     *
     * @param ViewModel $content
     */
    protected function renderCreateApplication(ViewModel $content)
    {
        return $content;
    }
}
