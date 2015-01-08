<?php

/**
 * Internal Variation Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\VariationOperatingCentreAdapter as CommonVariationOperatingCentreAdapter;

/**
 * Variation Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreAdapter extends CommonVariationOperatingCentreAdapter
{

    /**
     * Save a record (Extends the parent, but also persists the address)
     *
     * @param array $fileListData
     * @param array $formData
     */
    protected function saveRecord($fileListData, $formData)
    {
        $response = parent::saveRecord($fileListData, $formData);

        $data = $this->formatCrudDataForSave($formData);

        $this->getServiceLocator()->get('Entity\OperatingCentre')->save($data['operatingCentre']);

        return $response;
    }
}
