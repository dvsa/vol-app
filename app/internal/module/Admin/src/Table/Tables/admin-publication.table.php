<?php

return array(
    'variables' => array(
        'title' => 'Publications'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'generate' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'publish' => array('requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Publication No.',
            'name' => 'publicationNo',
            'sort' => 'publicationNo',
        ),
        array(
            'title' => 'Traffic Area',
            'name' => 'trafficArea',
            'formatter' => function ($row) {
                return $row['trafficArea']['name'];
            }
        ),
        array(
            'title' => 'Document Type',
            'name' => 'pubType',
        ),
        array(
            'title' => 'Publish date',
            'name' => 'pubDate',
            'sort' => 'pubDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
