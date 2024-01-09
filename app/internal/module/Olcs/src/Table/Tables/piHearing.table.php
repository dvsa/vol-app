<?php

use Olcs\Module;

return array(
    'variables' => array(
        'titleSingular' => 'Hearing',
        'title' => 'Hearings',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'addHearing' => array('class' => 'govuk-button', 'label' => 'Add'),
                'editHearing' => array(
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'requireRows' => true,
                    'label' => 'Edit'
                ),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ),
            ),
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
            'format' => '{{[elements/radio]}}',
            'hideWhenDisabled' => true
        ),
        array(
            'title' => 'Date of PI',
            'formatter' => function ($data) {
                $date = date(Module::$dateFormat, strtotime($data['hearingDate']));
                if (!empty($data['pi']['closedDate'])) {
                    return $date;
                } else {
                    $url = $this->generateUrl(
                        ['action' => 'edit', 'id' => $data['id'], 'pi' => $data['pi']['id']],
                        'case_pi_hearing', true
                    );
                    return '<a href="' . $url . '" class="govuk-link js-modal-ajax">' . $date . '</a>';
                }
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Venue',
            'formatter' => function ($data) {
                return (isset($data['venue']['name']) ? $data['venue']['name'] : $data['venueOther']);
            }
        ),
        array(
            'title' => 'Adjourned',
            'name' => 'isAdjourned'
        ),
        array(
            'title' => 'Cancelled',
            'name' => 'isCancelled'
        ),
        array(
            'title' => 'Hearing length',
            'formatter' => function ($data) {
                $hearingLength = 'Not known';
                if ($data['isFullDay'] == 'Y') {
                    $hearingLength = 'Full day';
                } elseif ($data['isFullDay'] == 'N') {
                    $hearingLength = 'Half day';
                }
                return $hearingLength;
            }
        ),
    )
);
