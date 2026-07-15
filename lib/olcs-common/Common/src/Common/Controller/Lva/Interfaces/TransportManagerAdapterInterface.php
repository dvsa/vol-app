<?php

/**
 * Transport Manager Adapter Interface
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Common\Controller\Lva\Interfaces;

/**
 * Transport Manager Adapter Interface
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
interface TransportManagerAdapterInterface extends AdapterInterface
{
    /**
     * get table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    public function getTable();

    /**
     * get table data
     *
     * @param int $applicationId application id
     * @param int $licenceId     licence id
     *
     * @return array|null
     */
    public function getTableData($applicationId, $licenceId);

    /**
     * whether at least one tm must be present
     *
     * @return int
     */
    public function mustHaveAtLeastOneTm();

    /**
     * delete transport manager
     *
     * @param array $ids           array of ids to be deleted
     * @param int   $applicationId application id
     *
     * @return bool
     */
    public function delete(array $ids, $applicationId);

    /**
     * add messages
     *
     * @param int $licenceId licence id
     *
     * @return void
     */
    public function addMessages($licenceId);

    /**
     * get number of rows in the data
     *
     * @param int $applicationId application id
     * @param int $licenceId     licence id
     *
     * @return int
     */
    public function getNumberOfRows($applicationId, $licenceId);
}
