<?php

/**
 * Crud Retrieve Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Common\Crud;

/**
 * Crud Retrieve Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
interface RetrieveInterface
{
    /**
     * Get's one single record.
     *
     * @param $id
     *
     * @return mixed
     */
    public function get($id);

    /**
     * Gets a list of records matching criteria.
     *
     * @param array $criteria Search / request criteria.
     *
     * @return array|null
     */
    public function getList(array $criteria = null);
}
