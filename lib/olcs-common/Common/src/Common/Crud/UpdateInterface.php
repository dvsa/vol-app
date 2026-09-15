<?php

/**
 * Crud Update Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Common\Crud;

/**
 * Crud Update Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
interface UpdateInterface
{
    /**
     * Updates a single record.
     *
     * @param array $data data to update
     *
     * @return bool
     */
    public function update(array $data, $id);
}
