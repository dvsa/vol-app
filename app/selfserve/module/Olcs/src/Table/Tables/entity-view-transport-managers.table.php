<?php

return array(
    'variables' => array(
        'empty_message' => 'entity-view-table-transport-managers.table.empty',
    ),
    'settings' => array(),
    'attributes' => array('id' => 'transport-managers'),
    'columns' => array(
        array(
            'title' => 'name',
            'formatter' => 'Name',
            'name' => 'transportManager->homeCd->person'
        )
    )
);
