<?php

declare(strict_types=1);

namespace Common\Auth\Adapter;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Auth\Login;
use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\Result;

/**
 * Class CommandAdapter
 * @see CommandAdapterFactory
 */
class CommandAdapter extends AbstractAdapter
{
    /**
     * This adapter is extended in selfserve/internal and realm is set accordingly
     *
     * @var string|null
     */
    protected $realm;

    /**
     * CognitoAdapter constructor.
     * @param $client
     */
    public function __construct(private CommandSender $commandSender)
    {
    }

    #[\Override]
    public function authenticate()
    {
        $command = Login::create([
            'username' => $this->getIdentity(),
            'password' => $this->getCredential(),
            'realm' => $this->realm
        ]);

        $result = $this->commandSender->send($command);

        if (!$result->isOk()) {
            $messages = $result->getResult()['messages'] ?? [];
            return new Result(Result::FAILURE, [], array_values($messages));
        }

        $flags = $result->getResult()['flags'];

        return new Result($flags['code'], $flags['identity'] ?? [], $flags['messages'] ?? []);
    }
}
