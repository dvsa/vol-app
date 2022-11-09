<?php

return array(
    'variables' => array(
        'titleSingular' => 'IRFO permit',
        'title' => 'IRFO permits'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary', 'requireRows' => false),
                'in-stock' => array(
                    'label' => 'In Stock', 'class' => 'action--secondary js-require--multiple', 'requireRows' => true
                ),
                'issued' => array('class' => 'action--secondary js-require--multiple', 'requireRows' => true),
                'void' => array('class' => 'action--secondary js-require--multiple', 'requireRows' => true),
                'returned' => array('class' => 'action--secondary js-require--multiple', 'requireRows' => true)
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
            'title' => 'Serial number',
            'isNumeric' => true,
            'name' => 'serialNo',
        ),
        array(
            'title' => 'Permit No',
            'isNumeric' => true,
            'formatter' => function ($data) {
                if (empty($data['irfoGvPermit']['id'])) {
                    return '';
                }

                return sprintf(
                    '<a href="%s" class="govuk-link js-modal-ajax">%s</a>',
                    $this->generateUrl(
                        array(
                            'action' => 'details',
                            'id' => $data['irfoGvPermit']['id'],
                            'organisation' => $data['irfoGvPermit']['organisation']['id']
                        ),
                        'operator/irfo/gv-permits',
                        false
                    ),
                    $data['irfoGvPermit']['id']
                );
            }
        ),
        array(
            'title' => 'Operator',
            'formatter' => function ($data) {
                if (empty($data['irfoGvPermit']['organisation']['id'])) {
                    return '';
                }

                return sprintf(
                    '<a class="govuk-link" href="%s">%s</a>',
                    $this->generateUrl(
                        array(
                            'organisation' => $data['irfoGvPermit']['organisation']['id']
                        ),
                        'operator/irfo/gv-permits',
                        false
                    ),
                    $data['irfoGvPermit']['organisation']['name']
                );
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data) {
                return $data['status']['description'];
            }
        ),
        array(
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ),
    )
);
