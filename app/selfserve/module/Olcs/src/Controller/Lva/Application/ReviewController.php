<?php

/**
 * External Application Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractReviewController;

/**
 * External Application Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReviewController extends AbstractReviewController
{
    protected $location = 'external';
    protected $lva = 'application';
}
