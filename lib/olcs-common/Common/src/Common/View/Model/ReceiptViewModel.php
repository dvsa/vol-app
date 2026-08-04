<?php

/**
 * Receipt View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\View\Model;

use Laminas\View\Model\ViewModel;

/**
 * Receipt View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReceiptViewModel extends ViewModel
{
    protected $terminate = true;

    protected $template = 'pages/fees/payment-success-print';
}
