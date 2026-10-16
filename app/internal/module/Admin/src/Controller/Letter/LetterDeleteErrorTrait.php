<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

/**
 * Shows the API's reason for refusing to delete letter content, which the generic handling
 * would replace with "unknown-error" because it has no translation.
 */
trait LetterDeleteErrorTrait
{
    /**
     * @param array $restResponse
     * @return void
     */
    #[\Override]
    public function handleErrors(array $restResponse)
    {
        $message = $restResponse['messages']['letterDelete'] ?? null;

        if (is_string($message) && $message !== '') {
            // flash messages are rendered unescaped and this one names admin-entered content
            $this->flashMessengerHelperService->addErrorMessage(htmlspecialchars($message, ENT_QUOTES));

            return;
        }

        parent::handleErrors($restResponse);
    }
}
