<?php

namespace Common\Service\FlashMessenger;

interface FlashMessengerInterface
{
    public function addMessage(string $message, ?string $namespace = null, int $hops = 1): self;
    public function addSuccessMessage(string $message): self;

    public function addErrorMessage(string $message): self;

    public function addWarningMessage(string $message): self;

    public function addInfoMessage(string $message): self;

    public function getNamespace(): string;

    public function setNamespace(string $namespace): self;

    public function getMessages(?string $namespace = null): array;
    public function getMessagesFromNamespace(string $namespace): array;

    public function hasMessages(?string $namespace = null): bool;

    public function clearCurrentMessagesFromContainer(): bool;

}
