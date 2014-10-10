<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Common\Controller\Traits\Lva;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractLicenceController
{
    use Lva\TypeOfLicenceTrait,
        Lva\LicenceTypeOfLicenceTrait;
}
