<?php

/**
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Date as DateValidator;

/**
 * Checks statement request date is not before the stop date
 */
class StopDateBeforeRequestDate extends DateRequired implements InputProviderInterface
{
    /**
     * @return (DateValidator|\Common\Form\Elements\Validators\DateLessThanOrEqual|\Common\Form\Elements\Validators\DateNotInFuture)[]
     *
     * @psalm-return list{\Common\Form\Elements\Validators\DateNotInFuture, \Common\Form\Elements\Validators\DateLessThanOrEqual, DateValidator}
     */
    #[\Override]
    public function getValidators()
    {
        return [
            new \Common\Form\Elements\Validators\DateNotInFuture(),
            new \Common\Form\Elements\Validators\DateLessThanOrEqual('requestedDate'),
            new DateValidator(['format' => 'Y-m-d'])
        ];
    }
}
