<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests;

use Fxp\Component\SmsSender\Exception\TransportException;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\SmsSender;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SmsSenderTest extends TestCase
{
    public function testSend(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();

        $transport->expects(static::once())
            ->method('send')
            ->with($message, $envelope)
        ;

        $sender = new SmsSender($transport);
        $sender->send($message, $envelope);
    }

    public function testSendWithBusMessenger(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();

        /** @var MessageBusInterface|MockObject $bus */
        $bus = $this->getMockBuilder(MessageBusInterface::class)->getMock();

        $bus->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(static function ($message, $stamp = []) use (&$busEnvelope) {
                $busEnvelope = new Envelope($message, $stamp);

                return $busEnvelope;
            })
        ;

        $sender = new SmsSender($transport, $bus);
        $sender->send($message, $envelope);

        static::assertInstanceOf(Envelope::class, $busEnvelope);
    }

    public function testHasRequiredFrom(): void
    {
        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects(static::once())->method('hasRequiredFrom')->willReturn(true);

        $sender = new SmsSender($transport);

        static::assertTrue($sender->hasRequiredFrom());
    }

    public function testSendWithRequiredFromAndWithoutFromInformation(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('The transport required the "From" information');

        $message = new Message();
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects(static::never())->method('send');
        $transport->expects(static::once())->method('hasRequiredFrom')->willReturn(true);

        $sender = new SmsSender($transport);
        $sender->send($message, $envelope);
    }
}
