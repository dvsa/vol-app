<?php

use Common\Service\Table\Formatter\Date;
use Olcs\Module;

return array(
    'variables' => array(
        'titleSingular' => 'Hearing',
        'title' => 'Hearings'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction',
            'actions' => array(
                'addHearing' => array('class' => 'govuk-button', 'value' => 'Add Hearing'),
                'editHearing' => array('requireRows' => true, 'value' => 'Edit Hearing')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Hearing Date',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'case_pi', true);

                $column['formatter'] = Date::class;
                return '<a class="govuk-link" href="' . $url . '">' . date(Module::$dateFormat, strtotime($data['hearingDate'])) . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Is Adjourned',
            'name' => 'isAdjourned'
        ),
        array(
            'title' => 'Venue',
            'name' => 'venue'
        ),
        array(
            'title' => 'Presiding TC',
            'formatter' => function ($data) {
                return $data['presidingTc']['name'];
            }
        ),
    )
);
