<?php

namespace Common\Service\Data\Interfaces;

/**
 * Interface Deletable
 * @package Common\Service\Data\Interfaces
 */
interface Deletable
{
    /**
     * Deletes item with id $id
     *
     * !! subject to change !!
     *
     * @param $id
     * @return Deletable
     */
    public function delete($id);
}
