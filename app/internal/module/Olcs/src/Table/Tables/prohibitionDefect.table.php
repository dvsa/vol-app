<?php

return array(
    'variables' => array(
        'titleSingular' => 'Prohibition defect',
        'title' => 'Prohibition defects'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Defect type',
            'formatter' => function ($data, $column) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'action' => 'edit',
                        'prohibition' => $data['prohibition']['id'],
                        'id' => $data['id']
                    ),
                    'case_prohibition_defect',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $data['defectType'] . '</a>';
            }
        ),
        array(
            'title' => 'Notes',
            'formatter' => 'Comment',
            'name' => 'notes',
        )
    )
);
