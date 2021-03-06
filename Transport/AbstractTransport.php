<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Transport;

use Fxp\Component\SmsSender\DelayedSmsEnvelope;
use Fxp\Component\SmsSender\Event\MessageEvent;
use Fxp\Component\SmsSender\Event\MessageResultEvent;
use Fxp\Component\SmsSender\Exception\TransportException;
use Fxp\Component\SmsSender\SentMessage;
use Fxp\Component\SmsSender\SmsEnvelope;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract class for the transport.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractTransport implements TransportInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var float
     */
    private $rate = 0.0;

    /**
     * @var float|int
     */
    private $lastSent = 0;

    /**
     * Constructor.
     *
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     */
    public function __construct(EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Sets the maximum number of messages to send per second (0 to disable).
     *
     * @param float $rate
     *
     * @return static
     */
    public function setMaxPerSecond(float $rate): self
    {
        if (0 >= $rate) {
            $rate = 0.0;
        }

        $this->rate = $rate;
        $this->lastSent = 0;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, SmsEnvelope $envelope = null): ?SentMessage
    {
        $message = clone $message;
        $sentMessage = null;

        if (null !== $envelope) {
            $envelope = clone $envelope;
        } else {
            try {
                /** @var Message $message */
                $envelope = new DelayedSmsEnvelope($message);
            } catch (\Exception $e) {
                throw new TransportException('Cannot send message without a valid envelope.', 0, $e);
            }
        }

        $event = new MessageEvent($message, $envelope);
        $this->dispatcher->dispatch($event);
        $envelope = $event->getEnvelope();

        if (!$envelope->getRecipients()) {
            return $sentMessage;
        }

        $sentMessage = new SentMessage($event->getMessage(), $envelope, new Result(\get_class($this)));

        $this->doSend($sentMessage);
        $this->dispatcher->dispatch(new MessageResultEvent(
            $sentMessage->getMessage(),
            $sentMessage->getEnvelope(),
            $sentMessage->getResult()
        ));

        $this->checkThrottling();

        return $sentMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequiredFrom(): bool
    {
        return true;
    }

    /**
     * Action to send the message.
     *
     * @param SentMessage $message The message
     */
    abstract protected function doSend(SentMessage $message): void;

    /**
     * Get the logger.
     *
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Check the throttling.
     */
    private function checkThrottling(): void
    {
        if (0.0 === $this->rate) {
            return;
        }

        $sleep = (1 / $this->rate) - (microtime(true) - $this->lastSent);

        if (0 < $sleep) {
            $this->getLogger()->debug(sprintf('SMS transport "%s" sleeps for %.2f seconds', \get_class($this), $sleep));
            usleep($sleep * 1000000);
        }

        $this->lastSent = microtime(true);
    }
}
