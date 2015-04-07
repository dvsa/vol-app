<?php

/**
 * Application Transport Manager Adapter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter as CommonApplicationTransportManagerAdapter;

/**
 * Application Transport Manager Adapter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ApplicationTransportManagerAdapter extends CommonApplicationTransportManagerAdapter
{
    /**
     * Get Table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    public function getTable()
    {
        // change the formater on the name column
        $table = parent::getTable();
        $column = $table->getColumn('name');
        $column['formatter'] = 'TransportManagerNameInternal';
        $table->setColumn('name', $column);

        // remove CRUD add and edit buttons
        $settings = $table->getSettings();
        unset($settings['crud']['actions']['add']);
        unset($settings['crud']['actions']['edit']);

        $table->setSettings($settings);

        return $table;
    }
}
