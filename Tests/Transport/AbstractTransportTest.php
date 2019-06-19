<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests\Transport;

use Fxp\Component\SmsSender\Exception\TransportException;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\AbstractTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractTransportTest extends TestCase
{
    /**
     * @throws
     */
    public function testSend(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);
        $transport->setMaxPerSecond(2 / 10);

        $transport->expects(static::atLeastOnce())
            ->method('doSend')
        ;

        $start = time();
        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(0, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(5, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(10, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(15, time() - $start, 1);

        $start = time();
        $transport->setMaxPerSecond(-3);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(0, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(0, time() - $start, 1);
    }

    /**
     * @throws
     */
    public function testSendWithEmptyRecipients(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), []);

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        $transport->expects(static::never())
            ->method('doSend')
        ;

        $transport->send($message, $envelope);
    }

    /**
     * @throws
     */
    public function testSendWithoutEnvelope(): void
    {
        $message = new Message();
        $message->getHeaders()->addMailboxListHeader('From', [new Phone('+100')]);
        $message->getHeaders()->addMailboxListHeader('To', [new Phone('+2000')]);

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        $transport->expects(static::once())
            ->method('doSend')
        ;

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testSendWithInvalidEnvelope(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Cannot send message without a valid envelope.');

        $message = new RawMessage('');

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        $transport->send($message);
    }
}
