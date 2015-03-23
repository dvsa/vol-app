<?php

/**
 * Internal Licence Type Of Licence Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\LicenceTypeOfLicenceAdapter as CommonLicenceTypeOfLicenceAdapter;

/**
 * Internal Licence Type Of Licence Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceTypeOfLicenceAdapter extends CommonLicenceTypeOfLicenceAdapter
{
    public function shouldDisableLicenceType($id, $applicationType = null)
    {
        return false;
    }

    public function doesChangeRequireConfirmation(array $postData, array $currentData)
    {
        return false;
    }

    public function setMessages($id = null, $applicationType = null)
    {
        // no-op
    }
}
