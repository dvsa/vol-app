<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\HideIfClosedRadio;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\YesNo;
use Olcs\Module;

return array(
    'variables' => array(
        'titleSingular' => 'Submission',
        'title' => 'Submissions'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'submission',
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'useQuery' => true
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'formatter' => HideIfClosedRadio::class
        ),
        array(
            'title' => 'Submission No.',
            'formatter' => function ($row) {
                return '<a class="govuk-link" href="' . $this->generateUrl(
                    array('submission' => $row['id'], 'action' => 'details'),
                    'submission',
                    true
                ) . '">' . $row['id'] . '</a>';
            },
            'sort' => 'id'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($row) {
                return $row['submissionType']['description'];
            },
            'name' => 'submissionType',
        ),
        array(
            'title' => 'Sub status',
            'formatter' => function ($row) {
                return !empty($row['closedDate']) ? 'Closed' : 'Open';
            },
        ),
        array(
            'title' => 'Date created',
            'formatter' => function ($row) {
                return date(Module::$dateTimeSecFormat, strtotime($row['createdOn']));
            },
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Date closed',
            'formatter' => Date::class,
            'name' => 'closedDate'
        ),
        array(
            'title' => 'Currently with',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Name::class;
                if (!empty($data['recipientUser']['contactDetails']['person'])) {
                    return $this->callFormatter($column, $data['recipientUser']['contactDetails']['person']);
                }
                if (!empty($data['createdBy']['contactDetails']['person'])) {
                    return $this->callFormatter($column, $data['createdBy']['contactDetails']['person']);
                }
                return '';
            }
        ),
        array(
            'title' => 'Date assigned',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'name' => 'assignedDate'
        ),
        array(
            'title' => 'Urgent',
            'formatter' => YesNo::class,
            'name' => 'urgent'
        )
    )
);
