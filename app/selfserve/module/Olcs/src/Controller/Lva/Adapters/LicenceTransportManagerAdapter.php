<?php

/**
 * External Licence Transport Manager Adater
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\LicenceTransportManagerAdapter as CommonAdapter;

/**
 * External Licence Transport Manager Adater
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceTransportManagerAdapter extends CommonAdapter
{
    /**
     * Add messages
     */
    public function addMessages($licenceId)
    {
        // add message saying to create a variation
        $this->getServiceLocator()->get('Lva\Variation')->addVariationMessage($licenceId, 'transport_managers');
    }
}
