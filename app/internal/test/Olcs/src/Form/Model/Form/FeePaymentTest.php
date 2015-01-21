<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class FeePaymentTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class FeePaymentTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\FeePayment';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['details', 'paymentType'],
                [
                    'fpm_card_offline' => 'Card',
                    'fpm_cash'         => 'Cash',
                    'fpm_cheque'       => 'Cheque',
                    'fpm_po'           => 'PO',
                ]
            ],
        ];
    }

    protected function getFormData()
    {
        $sm  = $this->getServiceManager();
        $dateHelper = $sm->get('Helper\Date');

        $today    = $dateHelper->getDateObject();
        $todayArr = [
            'day' => $today->format('d'),
            'month' => $today->format('m'),
            'year' => $today->format('y')
        ];
        $yesterday = $dateHelper->getDateObject('yesterday');
        $yesterdayArr = [
            'day' => $yesterday->format('d'),
            'month' => $yesterday->format('m'),
            'year' => $yesterday->format('y')
        ];
        $tomorrow = $dateHelper->getDateObject('tomorrow');
        $tomorrowArr = [
            'day' => $tomorrow->format('d'),
            'month' => $tomorrow->format('m'),
            'year' => $tomorrow->format('y')
        ];

        $cardContext = new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_card_offline');
        $cashContext = new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_cash');
        $chequeContext = new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_cheque');
        $poContext = new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_po');
        $feeAmountContext = new F\Context(new F\Stack(['details', 'feeAmountForValidator']), '250');

        return [
            new F\Test(
                new F\Stack(['details', 'paymentType']),
                new F\Value(F\Value::VALID, 'fpm_foo'),
                new F\Value(F\Value::VALID, 'fpm_bar'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['details', 'received']),
                new F\Value(F\Value::VALID, '1', $cardContext),
                new F\Value(F\Value::VALID, '1.01', $cardContext),
                new F\Value(F\Value::INVALID, '-1'),
                new F\Value(F\Value::INVALID, '0'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'not a number'),
                // for card, received amount ignored
                new F\Value(F\Value::VALID, '10', $cardContext, $feeAmountContext),
                // for cash, received amount must match fee amount
                new F\Value(F\Value::INVALID, '10', $cashContext, $feeAmountContext),
                new F\Value(F\Value::VALID, '250', $cashContext, $feeAmountContext),
                new F\Value(F\Value::VALID, '250.00', $cashContext, $feeAmountContext),
                // for cheques, received amount must match fee amount
                new F\Value(F\Value::INVALID, '10', $chequeContext, $feeAmountContext),
                new F\Value(F\Value::VALID, '250', $chequeContext, $feeAmountContext),
                new F\Value(F\Value::VALID, '250.00', $chequeContext, $feeAmountContext),
                // for Postal Orders, received amount must match fee amount
                new F\Value(F\Value::INVALID, '10', $poContext, $feeAmountContext),
                new F\Value(F\Value::VALID, '250', $poContext, $feeAmountContext),
                new F\Value(F\Value::VALID, '250.00', $poContext, $feeAmountContext)
            ),
            new F\Test(
                new F\Stack(['details', 'receiptDate']),
                new F\Value(F\Value::VALID, ['day'=>'05', 'month'=>'01', 'year'=>'2015']),
                new F\Value(F\Value::VALID, $todayArr),
                new F\Value(F\Value::VALID, $yesterdayArr),
                new F\Value(F\Value::INVALID, $tomorrowArr),
                // null receiptDate is only valid for card payments
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::VALID, null, $cardContext),
                new F\Value(F\Value::INVALID, null, $cashContext),
                new F\Value(F\Value::INVALID, null, $chequeContext),
                new F\Value(F\Value::INVALID, null, $poContext)
            ),

            // payer is required for everything except card payments
            new F\Test(
                new F\Stack(['details', 'payer']),
                new F\Value(F\Value::VALID, 'the payer name'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::VALID, null, $cardContext),
                new F\Value(F\Value::INVALID, null, $cashContext),
                new F\Value(F\Value::INVALID, null, $chequeContext),
                new F\Value(F\Value::INVALID, null, $poContext)
            ),

            // slip number is required for everything except card payments
            new F\Test(
                new F\Stack(['details', 'slipNo']),
                new F\Value(F\Value::VALID, 'X123'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::VALID, null, $cardContext),
                new F\Value(F\Value::INVALID, null, $cashContext),
                new F\Value(F\Value::INVALID, null, $chequeContext),
                new F\Value(F\Value::INVALID, null, $poContext)
            ),

            // cheque number is only required for cheque payments (duh)
            new F\Test(
                new F\Stack(['details', 'chequeNo']),
                new F\Value(F\Value::VALID, '123456'),
                new F\Value(F\Value::INVALID, null, $chequeContext),
                new F\Value(F\Value::VALID, null, $cardContext),
                new F\Value(F\Value::VALID, null, $cashContext),
                new F\Value(F\Value::VALID, null, $poContext)
            ),

            // PO number is only required for Postal Order payments (duh)
            new F\Test(
                new F\Stack(['details', 'poNo']),
                new F\Value(F\Value::VALID, '123456'),
                new F\Value(F\Value::INVALID, null, $poContext),
                new F\Value(F\Value::VALID, null, $cardContext),
                new F\Value(F\Value::VALID, null, $cashContext),
                new F\Value(F\Value::VALID, null, $chequeContext)
            ),
        ];
    }
}
