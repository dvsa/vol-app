<?php

namespace Dvsa\Olcs\Email\Transport;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;

/**
 * Mail Transport that allows sending to multiple Transports
 */
class MultiTransport implements TransportInterface
{
    /**
     * @var MultiTransportOptions
     */
    protected $options;

    /**
     * Sets options
     *
     * @param MultiTransportOptions $options Options
     *
     * @return void
     */
    public function setOptions(MultiTransportOptions $options)
    {
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return MultiTransportOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Send an email through each of the transports
     *
     * @param Message $message Message to send
     *
     * @return void
     */
    public function send(Message $message)
    {
        foreach ($this->getOptions()->getTransport() as $transport) {
            $transport->send($message);
        }
    }
}
