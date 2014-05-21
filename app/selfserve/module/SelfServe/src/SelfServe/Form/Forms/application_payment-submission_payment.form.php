<?php

return array(
    'application_payment-submission_payment' => array(
        'name' => 'application_payment-submission_payment',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' =>  array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => 'Secure payment information',
                    'hint' => 'To submit your application, please enter your card details below'
                ),
                'elements' => array(
                    'cardTypes' => array(
                        'type' => 'select',
                        'label' => 'Card types',
                        'value_options' => [
                            'Visa Credit',
                            'Visa Debit',
                            'MasterCard'
                        ],
                        'required' => true
                    ),
                    'name' => array(
                        'type' => 'text',
                        'label' => 'Name (as it appears on your card)'
                    ),
                    'cardNumber' => array(
                        'type' => 'text',
                        'label' => 'Card number (No dashes or spaces)'
                    ),
                    'expiryDate' => array(
                        'type' => 'html',
                        'label' => 'Expiry date',
                        'attributes' => array(
                            'value' => '<select><option>Please select</option></select>
                            <select><option>Please select</option></select>'
                        )
                    ),
                    'securityCode' => array(
                        'type' => 'text',
                        'label' => 'Security code',
                        'attributes' => array(
                            'class' => 'short'
                        )
                    ),
                    'amount' => array(
                        'type' => 'html',
                        'attributes' => array(
                            'value' => '<div class="highlight-box">Fee payable for the application <h2>&pound;254</h2>
                                After submitting your application no further changes can be made</div>'
                        )
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
