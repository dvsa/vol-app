<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

class InternalConversationMessage extends AbstractConversationMessage
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
                    {firstReadBy}
                </div>
            </div>
        </div>
    ';
}
