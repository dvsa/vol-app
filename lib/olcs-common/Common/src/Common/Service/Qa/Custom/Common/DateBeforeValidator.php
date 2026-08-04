<?php

namespace Common\Service\Qa\Custom\Common;

use Common\Service\Qa\DateTimeFactory;
use DateTime;
use IntlDateFormatter;
use Laminas\I18n\View\Helper\DateFormat;
use Laminas\Validator\AbstractValidator;

class DateBeforeValidator extends AbstractValidator
{
    public const ERR_DATE_NOT_BEFORE = 'date_not_before';

    /** @var array */
    protected $messageTemplates = [
        self::ERR_DATE_NOT_BEFORE => 'Date is too far away'
    ];

    /** @var array */
    protected $messageVariables = [
        'dateMustBeBefore'  => 'formattedDateMustBeBefore',
    ];

    public string $formattedDateMustBeBefore;

    /**
     * Create service instance
     *
     *
     * @return DateBeforeValidator
     */
    public function __construct(private DateFormat $dateFormat, private DateTimeFactory $dateTimeFactory, array $options)
    {
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isValid($value)
    {
        $dateMustBeBefore = $this->getOption('dateMustBeBefore');
        $formattedValue = (new DateTime($value))->format('Y-m-d');

        $valid = $formattedValue < $dateMustBeBefore;
        if (!$valid) {
            $dateMustBeBeforeDateTime = $this->dateTimeFactory->create($dateMustBeBefore);

            $this->formattedDateMustBeBefore = $this->dateFormat->__invoke(
                $dateMustBeBeforeDateTime,
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE
            );

            $this->error(self::ERR_DATE_NOT_BEFORE);
        }

        return $valid;
    }
}
