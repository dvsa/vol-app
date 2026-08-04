<?php

/**
 * Conditions Undertakings Adapter Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Lva\Interfaces;

use Laminas\Form\Form;

/**
 * Conditions Undertakings Adapter Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ConditionsUndertakingsAdapterInterface
{
    /**
     * Check whether we can update the record
     *
     * @param int $id
     * @param int $parentId
     * @return bool
     */
    public function canEditRecord($data);

    /**
     * Alter the form based upon the id
     *
     * @param array $data
     */
    public function alterForm(Form $form, $data);

    /**
     * Process the data for saving
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    public function processDataForSave($data, $id);
}
