<?php

return array(
    'variables' => array(
        'title' => 'Complaints',
        'titleSingular' => 'Complaint',
        'empty_message' => 'There are no complaints'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button', 'label' => 'Add complaint'),
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
            'title' => 'Date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'complaint' => $data['id']),
                    'case_complaint',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant name',
            'formatter' => function ($data, $column) {
                return $data['complainantContactDetails']['person']['forename'] . ' ' .
                $data['complainantContactDetails']['person']['familyName'];
            }
        ),
        array(
            'title' => 'Description',
            'formatter' => 'Comment',
            'name' => 'description'
        )
    )
);
