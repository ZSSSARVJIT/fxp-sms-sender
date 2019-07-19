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

use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Exception\LogicException;
use Fxp\Component\SmsSender\Transport;
use Fxp\Component\SmsSender\Transport\AbstractTransport;
use Fxp\Component\SmsSender\Transport\FailoverTransport;
use Fxp\Component\SmsSender\Transport\NullTransport;
use Fxp\Component\SmsSender\Transport\RoundRobinTransport;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class TransportTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected function setUp(): void
    {
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
        $this->httpClient = null;
        $this->logger = null;
    }

    public function testFromDsnNull(): void
    {
        $transport = Transport::fromDsn('sms://null', $this->dispatcher, $this->httpClient, $this->logger);

        static::assertInstanceOf(NullTransport::class, $transport);
        $this->validateDispatcher($transport);
    }

    public function testFromDsnNullWithInvalidScheme(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "api" scheme is not supported for SMS Sender "null". Supported schemes are: "sms".');

        Transport::fromDsn('api://null', $this->dispatcher, $this->httpClient, $this->logger);
    }

    public function testFromDsnFailover(): void
    {
        $transport = Transport::fromDsn('sms://null || sms://null', $this->dispatcher, $this->httpClient, $this->logger);
        static::assertInstanceOf(FailoverTransport::class, $transport);
    }

    public function testFromDsnRoundRobin(): void
    {
        $transport = Transport::fromDsn('sms://null && sms://null', $this->dispatcher, $this->httpClient, $this->logger);
        static::assertInstanceOf(RoundRobinTransport::class, $transport);
    }

    public function testFromInvalidDsn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "sms://" SMS Sender DSN is invalid.');

        Transport::fromDsn('sms://');
    }

    public function testFromInvalidDsnNoHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "?!" SMS Sender DSN must contain a transport scheme.');

        Transport::fromDsn('?!');
    }

    public function testFromInvalidTransportName(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "foobar" SMS Sender is not supported.');

        Transport::fromDsn('sms://foobar');
    }

    private function validateDispatcher(TransportInterface $transport): void
    {
        $p = new \ReflectionProperty(AbstractTransport::class, 'dispatcher');
        $p->setAccessible(true);
        static::assertSame($this->dispatcher, $p->getValue($transport));
    }
}
