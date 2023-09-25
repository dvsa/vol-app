<?php

use Common\Service\Table\Formatter\RefData;

return array(
    'variables' => array(
        'title' => 'Serious Infringements',
        'titleSingular' => 'Serious Infringement'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one'),
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Id',
            'isNumeric' => true,
            'formatter' => function ($data) {
                return sprintf(
                    '<a href="%s" class="govuk-link js-modal-ajax">%s</a>',
                    $this->generateUrl(array('action' => 'edit', 'id' => $data['id']), 'case_penalty'),
                    $data['id']
                );
            }
        ),
        array(
            'title' => 'Category',
            'formatter' => RefData::class,
            'name' => 'siCategoryType'
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
