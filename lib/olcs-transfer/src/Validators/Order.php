<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * Order validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Order extends \Laminas\Validator\AbstractValidator
{
    public const NOT_ASC_OR_DESC = 'notAscOrDesc';

    protected $messageTemplates = [
        self::NOT_ASC_OR_DESC => 'The order was not ASC or DESC',
    ];

    #[\Override]
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::NOT_ASC_OR_DESC);
            return false;
        }

        $values = explode(',', $value);
        foreach ($values as $order) {
            $order = trim(strtoupper($order));
            if ($order !== 'ASC' && $order !== 'DESC') {
                $this->error(self::NOT_ASC_OR_DESC);
                return false;
            }
        }

        return true;
    }
}
