<?php

/**
 * Internal Application Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\AbstractReviewController;

/**
 * Internal Application Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReviewController extends AbstractReviewController
{
    // @NOTE at the moment we use this trait here for its preDispatch method, which flips us to a variation root
    // if the given application id is a variation
    use ApplicationControllerTrait;

    protected $location = 'internal';
    protected $lva = 'application';
}