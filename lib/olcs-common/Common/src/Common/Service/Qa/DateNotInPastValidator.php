<?php

namespace Common\Service\Qa;

use DateTime;
use Laminas\Validator\AbstractValidator;

class DateNotInPastValidator extends AbstractValidator
{
    public const ERR_DATE_IN_PAST = 'date_in_past';

    /** @var array */
    protected $messageTemplates = [
        self::ERR_DATE_IN_PAST => 'Date is in the past'
    ];

    /**
     * Create service instance
     *
     *
     * @return DateNotInPastValidator
     */
    public function __construct(private DateTimeFactory $dateTimeFactory, array $options)
    {
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isValid($value)
    {
        $formattedCurrentDateTime = $this->dateTimeFactory->create()->format('Y-m-d');
        $formattedValue = (new DateTime($value))->format('Y-m-d');

        $valid = $formattedValue >= $formattedCurrentDateTime;
        if (!$valid) {
            $this->error(self::ERR_DATE_IN_PAST);
        }

        return $valid;
    }
}
