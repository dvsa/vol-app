<?php

/**
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 *
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Date as DateValidator;

/**
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 *
 */
class DateNotInFuture extends DateRequired implements InputProviderInterface
{
    /**
     * @return (string|string[])[][]
     *
     * @psalm-return list{array{name: 'Date', options: array{format: 'Y-m-d'}}, array{name: \Common\Form\Elements\Validators\DateNotInFuture::class}}
     */
    #[\Override]
    public function getValidators()
    {
        return [
            ['name' => 'Date', 'options' => ['format' => 'Y-m-d']],
            ['name' => \Common\Form\Elements\Validators\DateNotInFuture::class]
        ];
    }
}
