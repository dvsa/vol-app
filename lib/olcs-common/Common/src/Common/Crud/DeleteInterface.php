<?php

/**
 * Crud Delete Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Common\Crud;

/**
 * Crud Delete Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
interface DeleteInterface
{
    /**
     * Deletes a single record.
     *
     * @param $id
     *
     * @return bool
     */
    public function delete($id);
}
