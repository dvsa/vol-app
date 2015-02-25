<?php

/**
 * External Application Type of Licence Adapter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\ApplicationTypeOfLicenceAdapter as CommonApplicationTypeOfLicenceAdapter;
use Common\Service\Helper\FormHelperService;

/**
 * External Application Type of Licence Adapter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTypeOfLicenceAdapter extends CommonApplicationTypeOfLicenceAdapter
{
    /**
     * Create a task for the new fee - don't need to create task externally
     * 
     * @param int $applicationId
     * @param int $licenceId
     * @return int|null
     */
    protected function createTask($applicationId, $licenceId)
    {
    }
}
