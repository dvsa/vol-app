<?php

use Common\Service\Table\Formatter\Date;
use Olcs\Module;

return array(
    'variables' => array(
        'titleSingular' => 'Publication',
        'title' => 'Publications'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
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
            'title' => 'Created',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                $lva = (isset($column['lva'])) ? $column['lva'] : 'licence';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id']),
                    $lva .'/processing/publications',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'createdOn',
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Publication No.',
            'isNumeric' => true,
            'formatter' => function ($data) {
                return $data['publication']['publicationNo'];
            }
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data) {
                return $data['publication']['pubType'];
            }
        ),
        array(
            'title' => 'Traffic area',
            'formatter' => function ($data) {
                return $data['publication']['trafficArea']['name'];
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data) {
                return $data['publication']['pubStatus']['description'];
            }
        ),
        array(
            'title' => 'Publication date',
            'formatter' => function ($data) {
                $date = new DateTime($data['publication']['pubDate']);
                return $date->format(Module::$dateFormat);
            }
        ),
        array(
            'title' => 'Section',
            'formatter' => function ($data) {
                return $data['publicationSection']['description'];
            }
        ),
        array(
            'title' => 'Text',
            'formatter' => function ($data) {
                $string = nl2br($data['text1']) . '<br />' . $data['text2'];
                if (strlen($string) > 100) {
                    return substr($string, 0, 100) . ' [...]';
                }

                return $string;
            }
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
