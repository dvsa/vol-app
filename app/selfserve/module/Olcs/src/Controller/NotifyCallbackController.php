<?php

namespace Olcs\Controller;

use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Exception as CqrsException;
use Dvsa\Olcs\Transfer\Command\Notify\UpdateDeliveryStatus;
use Laminas\Mvc\Controller\AbstractActionController;
use Olcs\Logging\Log\Logger;

/**
 * Delivery status callbacks from GOV.UK Notify. The API can't be called by Notify directly, so the bearer
 * token is checked here and the status is passed on to the API as an anonymous command.
 */
class NotifyCallbackController extends AbstractActionController
{
    public function __construct(private string $token, private CommandSender $commandSender)
    {
    }

    #[\Override]
    public function indexAction()
    {
        $response = $this->getResponse();

        if (!$this->hasValidToken()) {
            Logger::warn('Notify callback rejected, missing or wrong token');

            return $response->setStatusCode(401);
        }

        $data = json_decode($this->getRequest()->getContent(), true);

        if (!is_array($data)) {
            return $response->setStatusCode(400);
        }

        try {
            $result = $this->commandSender->send(
                UpdateDeliveryStatus::create([
                    'id' => $data['id'] ?? null,
                    'status' => $data['status'] ?? null,
                    'completedAt' => $data['completed_at'] ?? null,
                    'sentAt' => $data['sent_at'] ?? null,
                    'reference' => $data['reference'] ?? null,
                ])
            );
        } catch (CqrsException | ResourceConflictException $e) {
            // the API call failed, so ask Notify to try again
            Logger::warn('Notify callback failed in the API: ' . $e->getMessage());

            return $response->setStatusCode(500);
        }

        return $response->setStatusCode($result->isOk() ? 200 : 400);
    }

    private function hasValidToken(): bool
    {
        // blank, " " and "null" mean the token isn't set up for this environment
        $token = trim($this->token);

        if (in_array($token, ['', 'null'], true)) {
            return false;
        }

        $header = $this->getRequest()->getHeader('Authorization');
        $bearer = $header ? $header->getFieldValue() : '';

        return hash_equals('Bearer ' . $token, $bearer);
    }
}
