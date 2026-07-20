<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkDocument;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkEvent;

/**
 * Builds a Retrieve-via-Link bundle from a set of documents and returns the persisted link.
 *
 * Called by email flows (e.g. Publications) INSTEAD of attaching the files: the caller then puts
 * `http://selfserve/retrieve/{token}` in the email body, which the existing SendEmail URI
 * rewriting turns into the real selfserve URL. Real document ids stay server-side — only the
 * link token and per-member opaque refs are ever exposed.
 */
final class RetrievalLinkCreator
{
    public function __construct(
        private readonly RepositoryServiceManager $repositoryServiceManager,
        private readonly TokenGenerator $tokenGenerator,
        private readonly RetrievalPolicyResolver $policyResolver,
    ) {
    }

    /**
     * @param array<int, int> $documentIds ids of already-stored documents to bundle
     *
     * @throws \RuntimeException when an otp-gated flow is given no recipient address to send the code to
     */
    public function create(
        array $documentIds,
        ?string $recipientEmail,
        string $flowKey,
        ?string $sourceContext = null,
    ): RetrievalLink {
        $policy = $this->policyResolver->resolve($flowKey);

        if ($policy->requiresOtp() && ($recipientEmail === null || trim($recipientEmail) === '')) {
            throw new \RuntimeException(
                sprintf('Retrieval flow "%s" is OTP-gated but no recipient email was supplied for the code', $flowKey),
            );
        }

        $now = new \DateTimeImmutable();

        $link = new RetrievalLink();
        $link->setToken($this->tokenGenerator->generate());
        $link->setGateMode($policy->gate);
        $link->setFlowKey($flowKey);
        $link->setSourceContext($sourceContext);
        // Only OTP flows need to retain the recipient address (to send the code to).
        $link->setRecipientEmail($policy->requiresOtp() ? $recipientEmail : null);
        $link->setExpiresAt(\DateTime::createFromInterface($policy->expiresFrom($now)));

        $this->repositoryServiceManager->get('RetrievalLink')->save($link);

        $documentRepo = $this->repositoryServiceManager->get('Document');
        $memberRepo = $this->repositoryServiceManager->get('RetrievalLinkDocument');

        $order = 0;
        foreach ($documentIds as $documentId) {
            /** @var DocumentEntity $document */
            $document = $documentRepo->fetchById($documentId);

            $member = new RetrievalLinkDocument();
            $member->setRetrievalLink($link);
            $member->setDocument($document);
            $member->setMemberRef($this->tokenGenerator->generate());
            $member->setDisplayFilename(basename((string) $document->getFilename()));
            $member->setDisplayOrder($order++);

            $memberRepo->save($member);
        }

        $this->recordCreated($link);

        return $link;
    }

    private function recordCreated(RetrievalLink $link): void
    {
        $event = new RetrievalLinkEvent();
        $event->setRetrievalLink($link);
        $event->setEventType('created');
        $event->setDetail($link->getFlowKey());

        $this->repositoryServiceManager->get('RetrievalLinkEvent')->save($event);
    }
}
