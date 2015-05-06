<?php

/**
 * Receipt View Model
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\View\Model;

use Zend\View\Model\ViewModel;

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
