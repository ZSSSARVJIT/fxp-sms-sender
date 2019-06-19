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
     * @param TransportInterface[] $transports
     * @param int                  $retryPeriod
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
     * Rotates the transport list around and returns the first instance.
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

    protected function isTransportDead(TransportInterface $transport): bool
    {
        return $this->deadTransports->contains($transport);
    }

    private function moveCursor(int $cursor): int
    {
        return ++$cursor >= \count($this->transports) ? 0 : $cursor;
    }
}
