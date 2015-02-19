<?php

/**
 * Internal Variation Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractReviewController;

/**
 * Internal Variation Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReviewController extends AbstractReviewController
{
    protected $location = 'internal';
    protected $lva = 'variation';
}
