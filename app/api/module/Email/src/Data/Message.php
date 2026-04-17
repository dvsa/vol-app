<?php

namespace Dvsa\Olcs\Email\Data;

use Dvsa\Olcs\Email\Domain\Command\SendEmail;

/**
 * Email Message
 *
 * @NOTE This has been left in for backwards compatibility, this essentially creates a command
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Message
{
    protected $fromName;

    protected $fromEmail;

    protected $to;

    protected $cc = [];

    protected $bcc = [];

    protected $subject;

    protected $subjectVariables = [];

    protected $docs = [];

    protected $plainBody;

    protected $htmlBody;

    protected $highPriority = false;

    protected $hasHtml = true;

    protected $locale = 'en_GB';

    public function __construct($to, $subject)
    {
        $this->setTo($to);
        $this->setSubject($subject);
    }

    public function buildCommand()
    {
        $data = [
            'fromName' => $this->getFromName(),
            'fromEmail' => $this->getFromEmail(),
            'to' => $this->getTo(),
            'cc' => $this->getCc(),
            'bcc' => $this->getBcc(),
            'subject' => $this->getSubject(),
            'subjectVariables' => $this->getSubjectVariables(),
            'docs' => $this->getDocs(),
            'plainBody' => $this->getPlainBody(),
            'htmlBody' => $this->getHtmlBody(),
            'highPriority' => $this->isHighPriority(),
            'hasHtml' => $this->getHasHtml(),
            'locale' => $this->getLocale()
        ];

        return SendEmail::create($data);
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getSubjectReplaceVariables()
    {
        return vsprintf($this->getSubject(), $this->getSubjectVariables());
    }

    /**
     * @return string
     */
    public function getPlainBody()
    {
        return $this->plainBody;
    }

    /**
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @return bool
     */
    public function getHasHtml()
    {
        return $this->hasHtml;
    }

    /**
     *
     * @param string $fromName
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @param string $fromEmail
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    /**
     * @param string $to
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setTo($to)
    {
        $this->to = trim($to);
        return $this;
    }

    /**
     * @param string $subject
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param string $plainBody
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setPlainBody($plainBody)
    {
        $this->plainBody = $plainBody;
        return $this;
    }

    /**
     * @param string $htmlBody
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return $this->highPriority;
    }

    public function setHighPriority(bool $highPriority = true): void
    {
        $this->highPriority = $highPriority;
    }

    /**
     * @param bool $hasHtml
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setHasHtml($hasHtml)
    {
        $this->hasHtml = $hasHtml;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubjectVariables()
    {
        return $this->subjectVariables;
    }

    /**
     * Set variables to be replace in the subject
     * NB uses sprintf
     *
     * @param array $subjectVariables [var1, var2, etc]
     *
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setSubjectVariables(array $subjectVariables)
    {
        $this->subjectVariables = $subjectVariables;
        return $this;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set the message cc field from a comma separated list of addresses
     *
     * @param string|null $listString comma separated list of addresses
     *
     * @return Message
     */
    public function setCcFromString(?string $listString): Message
    {
        $listArray = [];

        //convert the cc string into a suitable array
        if (isset($listString)) {
            $listArray = array_filter(array_map(trim(...), explode(',', $listString)));
        }

        return $this->setCc($listArray);
    }

    /**
     * Set cc email addresses
     *
     * @param array $cc [addr1, addr2, etc]
     *
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setCc(array $cc)
    {
        foreach ($cc as &$email) {
            $email = trim((string) $email);
        }
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set bcc email addresses
     *
     * @param array $bcc [addr1, addr2, etc]
     *
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setBcc(array $bcc)
    {
        foreach ($bcc as &$email) {
            $email = trim((string) $email);
        }
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return array
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * Set the document ids to attach to the email
     *
     * @param array $docs [doc1, doc2, etc]
     *
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setDocs(array $docs)
    {
        $this->docs = $docs;
        return $this;
    }

    /**
     * Translate message into Welsh
     *
     * @param string $yesNo 'Y' or 'N'
     *
     * @return \Dvsa\Olcs\Email\Data\Message
     */
    public function setTranslateToWelsh($yesNo)
    {
        if (strtoupper($yesNo) === 'Y') {
            $this->setLocale('cy_GB');
        }
        return $this;
    }
}
