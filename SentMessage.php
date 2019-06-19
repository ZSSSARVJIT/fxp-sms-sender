<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender;

use Fxp\Component\SmsSender\Transport\Result;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * Sent Message.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SentMessage
{
    /**
     * @var RawMessage
     */
    private $original;

    /**
     * @var RawMessage
     */
    private $raw;

    /**
     * @var SmsEnvelope
     */
    private $envelope;

    /**
     * @var Result
     */
    private $result;

    /**
     * Constructor.
     *
     * @param RawMessage  $message
     * @param SmsEnvelope $envelope
     * @param Result      $result
     */
    public function __construct(RawMessage $message, SmsEnvelope $envelope, Result $result)
    {
        $this->raw = $message instanceof Message ? new RawMessage($message->toIterable()) : $message;
        $this->original = $message;
        $this->envelope = $envelope;
        $this->result = $result;
    }

    public function getMessage(): RawMessage
    {
        return $this->raw;
    }

    public function getOriginalMessage(): RawMessage
    {
        return $this->original;
    }

    public function getEnvelope(): SmsEnvelope
    {
        return $this->envelope;
    }

    public function toString(): string
    {
        return $this->raw->toString();
    }

    public function toIterable(): iterable
    {
        return $this->raw->toIterable();
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
