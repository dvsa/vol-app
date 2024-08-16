<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Transaction;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment as ResolvePaymentCmd;
use Olcs\Logging\Log\Logger;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;

/**
 * Resolve Outstanding Payments
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ResolveOutstandingPayments extends AbstractCommandHandler implements
    AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Transaction';

    protected $extraRepos = ['SystemParameter'];

    /**
     * @inheritdoc
     */
    public function handleCommand(CommandInterface $command)
    {
        $minAge = $this->getRepo('SystemParameter')->fetchValue('RESOLVE_CARD_PAYMENTS_MIN_AGE');

        $transactions = $this->getRepo()->fetchOutstandingCardPayments($minAge);

        /* @var $transaction Transaction */
        foreach ($transactions as $transaction) {
            $transactionId = $transaction->getId();
            try {
                $cmd = ResolvePaymentCmd::create(['id' => $transactionId]);
                $this->result->merge($this->handleSideEffect($cmd));
            } catch (\Exception $e) {
                $message = 'Error resolving payment for transaction ' . $transactionId . ': ' . $e->getMessage();
                $this->result->addMessage($message);
                Logger::err($message);
            }
        }

        return $this->result;
    }
}
