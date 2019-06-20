<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Event;

use Fxp\Component\SmsSender\SmsEnvelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractMessageEvent extends Event
{
    /**
     * @var RawMessage
     */
    private $message;

    /**
     * @var SmsEnvelope
     */
    private $envelope;

    /**
     * Constructor.
     *
     * @param RawMessage  $message  The message
     * @param SmsEnvelope $envelope The envelope
     */
    public function __construct(RawMessage $message, SmsEnvelope $envelope)
    {
        $this->message = $message;
        $this->envelope = $envelope;
    }

    /**
     * Get the message.
     *
     * @return RawMessage
     */
    public function getMessage(): RawMessage
    {
        return $this->message;
    }

    /**
     * Set the message.
     *
     * @param RawMessage $message The message
     */
    public function setMessage(RawMessage $message): void
    {
        $this->message = $message;
    }

    /**
     * Get the envelope.
     *
     * @return SmsEnvelope
     */
    public function getEnvelope(): SmsEnvelope
    {
        return $this->envelope;
    }

    /**
     * Set the envelope.
     *
     * @param SmsEnvelope $envelope The envelope
     */
    public function setEnvelope(SmsEnvelope $envelope): void
    {
        $this->envelope = $envelope;
    }
}
