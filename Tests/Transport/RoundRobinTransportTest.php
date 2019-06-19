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
use Fxp\Component\SmsSender\Transport\RoundRobinTransport;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class RoundRobinTransportTest extends TestCase
{
    public function testSendNoTransports(): void
    {
        $this->expectException(TransportException::class);
        new RoundRobinTransport([]);
    }

    public function testSendAlternate(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(2))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::once())->method('send');

        $transport = new RoundRobinTransport([$transport1, $transport2]);
        $transport->send(new RawMessage(''));

        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);
    }

    public function testSendAllDead(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport = new RoundRobinTransport([$transport1, $transport2]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('All transports failed.');

        $transport->send(new RawMessage(''));

        $this->assertTransports($transport, 1, [$transport1, $transport2]);
    }

    public function testSendOneDead(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::exactly(3))->method('send');

        $transport = new RoundRobinTransport([$transport1, $transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);
    }

    public function testSendOneDeadAndRecoveryNotWithinRetryPeriod(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(4))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport = new RoundRobinTransport([$transport1, $transport2], 60);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, [$transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, [$transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, [$transport2]);
    }

    public function testSendOneDeadAndRecoveryWithinRetryPeriod(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(3))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::at(0))->method('send')->will(static::throwException(new TransportException()));
        $transport2->expects(static::at(1))->method('send');

        $transport = new RoundRobinTransport([$transport1, $transport2], 3);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, [$transport2]);

        sleep(3);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);
    }

    /**
     * @param RoundRobinTransport $transport
     * @param int                 $cursor
     * @param array               $deadTransports
     *
     * @throws
     */
    private function assertTransports(RoundRobinTransport $transport, int $cursor, array $deadTransports): void
    {
        $prop = new \ReflectionProperty($transport, 'cursor');
        $prop->setAccessible(true);
        static::assertSame($cursor, $prop->getValue($transport));

        $prop = new \ReflectionProperty($transport, 'deadTransports');
        $prop->setAccessible(true);
        static::assertSame($deadTransports, iterator_to_array($prop->getValue($transport)));
    }
}
