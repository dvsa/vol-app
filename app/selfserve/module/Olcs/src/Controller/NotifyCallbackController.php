<?php

namespace Olcs\Controller;

use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Exception as CqrsException;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Dvsa\Olcs\Transfer\Command\Notify\UpdateDeliveryStatus;
use Laminas\Mvc\Controller\AbstractActionController;
use Olcs\Logging\Log\Logger;

/**
 * Delivery status callbacks from GOV.UK Notify. The API can't be called by Notify directly, so the status is
 * passed on to the API as an anonymous command along with the bearer token, which the API checks.
 */
class NotifyCallbackController extends AbstractActionController
{
    public function __construct(private CommandSender $commandSender)
    {
    }

    #[\Override]
    public function indexAction()
    {
        $response = $this->getResponse();

        $token = $this->getBearerToken();

        if ($token === '') {
            Logger::warn('Notify callback rejected, no bearer token');

            return $response->setStatusCode(401);
        }

        $data = json_decode($this->getRequest()->getContent(), true);

        if (!is_array($data)) {
            return $response->setStatusCode(400);
        }

        try {
            $result = $this->commandSender->send(
                UpdateDeliveryStatus::create([
                    'token' => $token,
                    'id' => $data['id'] ?? null,
                    'status' => $data['status'] ?? null,
                    'completedAt' => $data['completed_at'] ?? null,
                    'sentAt' => $data['sent_at'] ?? null,
                    'reference' => $data['reference'] ?? null,
                ])
            );
        } catch (AccessDeniedException) {
            Logger::warn('Notify callback rejected by the API, missing or wrong token');

            return $response->setStatusCode(401);
        } catch (CqrsException | ResourceConflictException $e) {
            // the API call failed, so ask Notify to try again
            Logger::warn('Notify callback failed in the API: ' . $e->getMessage());

            return $response->setStatusCode(500);
        }

        return $response->setStatusCode($result->isOk() ? 200 : 400);
    }

    private function getBearerToken(): string
    {
        $header = $this->getRequest()->getHeader('Authorization');
        $value = $header ? $header->getFieldValue() : '';

        return str_starts_with($value, 'Bearer ') ? trim(substr($value, 7)) : '';
    }
}
