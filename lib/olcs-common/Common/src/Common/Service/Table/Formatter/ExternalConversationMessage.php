<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

class ExternalConversationMessage extends AbstractConversationMessage
{
    protected string $rowTemplate = '
        <div class="govuk-!-margin-bottom-6">
            <div class="govuk-summary-card">
                <div class="govuk-summary-card__title-wrapper">
                    <h2 class="govuk-summary-card__title">{senderName}</h2>
                    <h2 class="govuk-summary-card__title govuk-summary-card__date">{messageDate}</h2>
                </div>
                <div class="govuk-summary-card__content">
                    <p class="govuk-body">{messageBody}</p>
                    {caseworkerFooter}
                    {fileList}
                </div>
            </div>
        </div>
    ';

    #[\Override]
    protected function getSenderName(array $row): string
    {
        $senderName = $this->defaultSenderName;

        if (!empty($row['createdBy'])) {
            if (!empty($row['createdBy']['contactDetails']['person'])) {
                $person = $row['createdBy']['contactDetails']['person'];
                $senderName = $person['forename'];
                if (!$this->isInternalUser($row)) {
                    $senderName .= " " . $person['familyName'];
                }
            } else {
                $senderName = $row['createdBy']['loginId'];
            }
        }

        return $senderName;
    }
}
