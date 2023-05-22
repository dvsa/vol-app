<?php

return array(
    'variables' => array(
        'titleSingular' => 'Publications',
        'title' => 'Publications'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'generate' => array('requireRows' => true, 'class' => 'govuk-button js-require--one'),
                'publish' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one')
            )
        ),
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
            'formatter' => 'PublicationNumber',
            'name' => 'publicationNo',
            'sort' => 'publicationNo',
        ),
        array(
            'title' => 'Document Type',
            'name' => 'pubType',
        ),
        array(
            'title' => 'Document status',
            'formatter' => function ($data) {
                return $data['pubStatus']['description'];
            }
        ),
        array(
            'title' => 'Publication date',
            'name' => 'pubDate',
            'sort' => 'pubDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
