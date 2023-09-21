<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\PublicationNumber;

return array(
    'variables' => array(
        'title' => 'Published'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Traffic Area',
            'name' => 'trafficArea',
            'formatter' => function ($row) {
                return $row['trafficArea']['name'];
            }
        ),
        array(
            'title' => 'Publication No.',
            'isNumeric' => true,
            'formatter' => PublicationNumber::class,
            'name' => 'publicationNo',
            'sort' => 'publicationNo',
        ),
        array(
            'title' => 'Document Type',
            'name' => 'pubType',
        ),
        array(
            'title' => 'Publication date',
            'name' => 'pubDate',
            'sort' => 'pubDate',
            'formatter' => Date::class
        ),
    )
);
