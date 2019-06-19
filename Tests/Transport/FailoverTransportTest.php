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
use Fxp\Component\SmsSender\Transport\FailoverTransport;
use Fxp\Component\SmsSender\Transport\RoundRobinTransport;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class FailoverTransportTest extends TestCase
{
    public function testSendNoTransports(): void
    {
        $this->expectException(TransportException::class);
        new FailoverTransport([]);
    }

    public function testSendFirstWork(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(3))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::never())->method('send');

        $transport = new FailoverTransport([$transport1, $transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);
    }

    public function testSendAllDead(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport = new FailoverTransport([$transport1, $transport2]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('All transports failed.');

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1, $transport2]);
    }

    public function testSendOneDead(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::exactly(3))->method('send');

        $transport = new FailoverTransport([$transport1, $transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);
    }

    public function testSendOneDeadAndRecoveryWithinRetryPeriod(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::at(0))->method('send')->will(static::throwException(new TransportException()));
        $transport1->expects(static::at(1))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::at(0))->method('send');
        $transport2->expects(static::at(1))->method('send');
        $transport2->expects(static::at(2))->method('send');
        $transport2->expects(static::at(3))->method('send')->will(static::throwException(new TransportException()));

        $transport = new FailoverTransport([$transport1, $transport2], 6);

        $transport->send(new RawMessage('')); // transport1 > fail - transport2>sent
        $this->assertTransports($transport, 0, [$transport1]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport2 > sent
        $this->assertTransports($transport, 0, [$transport1]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport2 > sent
        $this->assertTransports($transport, 0, [$transport1]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport2 > fail - transport1>sent
        $this->assertTransports($transport, 1, [$transport2]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport1 > sent
        $this->assertTransports($transport, 1, [$transport2]);
    }

    public function testSendAllDeadWithinRetryPeriod(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::at(0))->method('send')->will(static::throwException(new TransportException()));
        $transport1->expects(static::once())->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::at(0))->method('send');
        $transport2->expects(static::at(1))->method('send');
        $transport2->expects(static::at(2))->method('send')->will(static::throwException(new TransportException()));
        $transport2->expects(static::exactly(3))->method('send');

        $transport = new FailoverTransport([$transport1, $transport2], 40);

        $transport->send(new RawMessage(''));

        sleep(4);
        $transport->send(new RawMessage(''));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('All transports failed.');

        sleep(4);
        $transport->send(new RawMessage(''));
    }

    public function testSendOneDeadButRecover(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::at(0))->method('send')->will(static::throwException(new TransportException()));
        $transport1->expects(static::at(1))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::at(0))->method('send');
        $transport2->expects(static::at(1))->method('send');
        $transport2->expects(static::at(2))->method('send')->will(static::throwException(new TransportException()));

        $transport = new FailoverTransport([$transport1, $transport2], 1);

        $transport->send(new RawMessage(''));

        sleep(1);
        $transport->send(new RawMessage(''));

        sleep(1);
        $transport->send(new RawMessage(''));
    }

    private function assertTransports(RoundRobinTransport $transport, int $cursor, array $deadTransports): void
    {
        $prop = new \ReflectionProperty(RoundRobinTransport::class, 'cursor');
        $prop->setAccessible(true);
        static::assertSame($cursor, $prop->getValue($transport));

        $prop = new \ReflectionProperty(RoundRobinTransport::class, 'deadTransports');
        $prop->setAccessible(true);
        static::assertSame($deadTransports, iterator_to_array($prop->getValue($transport)));
    }
}
