<?php

use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'titleSingular' => 'Impounding',
        'title' => 'Impoundings',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one')
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
            'title' => 'Application received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'impounding' => $data['id']),
                    'case_details_impounding',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'applicationReceiptDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column) {
                return $this->translator->translate($data['impoundingType']['id']);
            }
        ),
        array(
            'title' => 'Presiding TC/DTC/HTRU/DHTRU',
            'formatter' => function ($data) {
                return (isset($data['presidingTc']['name']) ? $data['presidingTc']['name'] : '');
            }
        ),
        array(
            'title' => 'Outcome',
            'formatter' => function ($data, $column) {
                return (isset($data['outcome']['id']) ? $this->translator->translate($data['outcome']['id']) : '');
            }
        ),
        array(
            'title' => 'Outcome sent',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'outcomeSentDate'

        ),
    )
);
