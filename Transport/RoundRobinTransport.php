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

use Fxp\Component\SmsSender\Exception\TransportException;
use Fxp\Component\SmsSender\Exception\TransportExceptionInterface;
use Fxp\Component\SmsSender\SentMessage;
use Fxp\Component\SmsSender\SmsEnvelope;
use Symfony\Component\Mime\RawMessage;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoundRobinTransport implements TransportInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $deadTransports;

    /**
     * @var TransportInterface[]
     */
    private $transports;

    /**
     * @var int
     */
    private $retryPeriod;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * Constructor.
     *
     * @param TransportInterface[] $transports  The transports
     * @param int                  $retryPeriod The retry period
     */
    public function __construct(array $transports, int $retryPeriod = 60)
    {
        if (empty($transports)) {
            throw new TransportException(\get_class($this).' must have at least one transport configured.');
        }

        $this->transports = $transports;
        $this->deadTransports = new \SplObjectStorage();
        $this->retryPeriod = $retryPeriod;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, SmsEnvelope $envelope = null): ?SentMessage
    {
        while ($transport = $this->getNextTransport()) {
            try {
                return $transport->send($message, $envelope);
            } catch (TransportExceptionInterface $e) {
                $this->deadTransports[$transport] = microtime(true);
            }
        }

        throw new TransportException('All transports failed.');
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequiredFrom(): bool
    {
        foreach ($this->transports as $transport) {
            if ($transport->hasRequiredFrom()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rotates the transport list around and returns the first instance.
     *
     * @return null|TransportInterface
     */
    protected function getNextTransport(): ?TransportInterface
    {
        $cursor = $this->cursor;
        $transport = null;

        while (true) {
            $transport = $this->transports[$cursor];

            if (!$this->isTransportDead($transport)) {
                break;
            }

            if ((microtime(true) - $this->deadTransports[$transport]) > $this->retryPeriod) {
                $this->deadTransports->detach($transport);

                break;
            }

            if ($this->cursor === $cursor = $this->moveCursor($cursor)) {
                return null;
            }
        }

        $this->cursor = $this->moveCursor($cursor);

        return $transport;
    }

    /**
     * Check if the transport is dead.
     *
     * @param TransportInterface $transport The transport
     *
     * @return bool
     */
    protected function isTransportDead(TransportInterface $transport): bool
    {
        return $this->deadTransports->contains($transport);
    }

    /**
     * Move the cursor on the next transport, or the first transport if all transports are tested.
     *
     * @param int $cursor The cursor position
     *
     * @return int
     */
    private function moveCursor(int $cursor): int
    {
        return ++$cursor >= \count($this->transports) ? 0 : $cursor;
    }
}
