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

use Fxp\Component\SmsSender\Exception\IncompleteDsnException;
use Fxp\Component\SmsSender\Exception\UnsupportedSchemeException;
use Fxp\Component\SmsSender\Transport\Dsn;
use Fxp\Component\SmsSender\Transport\TransportFactoryInterface;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class TransportFactoryTestCase extends TestCase
{
    protected const USER = 'u$er';
    protected const PASSWORD = 'pa$s';

    /**
     * @var null|EventDispatcherInterface|MockObject
     */
    protected $dispatcher;

    /**
     * @var null|HttpClientInterface|MockObject
     */
    protected $client;

    /**
     * @var null|LoggerInterface|MockObject
     */
    protected $logger;

    abstract public function getFactory(): TransportFactoryInterface;

    abstract public function supportsProvider(): iterable;

    abstract public function createProvider(): iterable;

    public function unsupportedSchemeProvider(): iterable
    {
        return [];
    }

    public function incompleteDsnProvider(): iterable
    {
        return [];
    }

    /**
     * @dataProvider supportsProvider
     *
     * @param Dsn  $dsn
     * @param bool $supports
     */
    public function testSupports(Dsn $dsn, bool $supports): void
    {
        $factory = $this->getFactory();

        static::assertSame($supports, $factory->supports($dsn));
    }

    /**
     * @dataProvider createProvider
     *
     * @param Dsn                $dsn
     * @param TransportInterface $transport
     */
    public function testCreate(Dsn $dsn, TransportInterface $transport): void
    {
        $factory = $this->getFactory();

        static::assertEquals($transport, $factory->create($dsn));
    }

    /**
     * @dataProvider unsupportedSchemeProvider
     *
     * @param Dsn $dsn
     */
    public function testUnsupportedSchemeException(Dsn $dsn): void
    {
        $factory = $this->getFactory();

        $this->expectException(UnsupportedSchemeException::class);
        $factory->create($dsn);
    }

    /**
     * @dataProvider incompleteDsnProvider
     *
     * @param Dsn $dsn
     */
    public function testIncompleteDsnException(Dsn $dsn): void
    {
        $factory = $this->getFactory();

        $this->expectException(IncompleteDsnException::class);
        $factory->create($dsn);
    }

    protected function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher ?? $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    protected function getClient(): HttpClientInterface
    {
        return $this->client ?? $this->client = $this->createMock(HttpClientInterface::class);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger ?? $this->logger = $this->createMock(LoggerInterface::class);
    }
}
