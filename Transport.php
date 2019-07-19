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

use Fxp\Component\SmsSender\Bridge\Amazon\Transport\SnsTransportFactory;
use Fxp\Component\SmsSender\Bridge\Twilio\Transport\TwilioTransportFactory;
use Fxp\Component\SmsSender\Exception\UnsupportedHostException;
use Fxp\Component\SmsSender\Transport\Dsn;
use Fxp\Component\SmsSender\Transport\FailoverTransport;
use Fxp\Component\SmsSender\Transport\NullTransportFactory;
use Fxp\Component\SmsSender\Transport\RoundRobinTransport;
use Fxp\Component\SmsSender\Transport\TransportFactoryInterface;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Transport
{
    private const FACTORY_CLASSES = [
        SnsTransportFactory::class,
        TwilioTransportFactory::class,
        NullTransportFactory::class,
    ];

    /**
     * @var TransportFactoryInterface[]
     */
    private $factories;

    /**
     * Constructor.
     *
     * @param TransportFactoryInterface[] $factories The sms sender transport factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    /**
     * Create the transport form the DSN and include the failover or round robin logic if necessary.
     *
     * @param string                        $dsn        The DSN to build the transport
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|HttpClientInterface      $client     The custom http client
     * @param null|LoggerInterface          $logger     The logger
     *
     * @return TransportInterface
     */
    public static function fromDsn(
        string $dsn,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        $factory = new self(self::getDefaultFactories($dispatcher, $client, $logger));

        return $factory->fromString($dsn);
    }

    /**
     * Create the transport from the dsn string.
     *
     * @param string $dsn The dsn
     *
     * @return TransportInterface
     */
    public function fromString(string $dsn): TransportInterface
    {
        // failover?
        $dsns = preg_split('/\s++\|\|\s++/', $dsn);

        if (\count($dsns) > 1) {
            return new FailoverTransport($this->createFromDsns($dsns));
        }

        // round robin?
        $dsns = preg_split('/\s++&&\s++/', $dsn);

        if (\count($dsns) > 1) {
            return new RoundRobinTransport($this->createFromDsns($dsns));
        }

        return $this->fromDsnObject(Dsn::fromString($dsn));
    }

    /**
     * Create the transport from the dsn instance.
     *
     * @param Dsn $dsn The dsn instance
     *
     * @return TransportInterface
     */
    public function fromDsnObject(Dsn $dsn): TransportInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return $factory->create($dsn);
            }
        }

        throw new UnsupportedHostException($dsn);
    }

    /**
     * Create the transports from the dsn strings.
     *
     * @param string[] $dsns The dsn strings
     *
     * @return TransportInterface[]
     */
    private function createFromDsns(array $dsns): array
    {
        $transports = [];

        foreach ($dsns as $dsn) {
            $transports[] = $this->fromDsnObject(Dsn::fromString($dsn));
        }

        return $transports;
    }

    /**
     * Get the default factories.
     *
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|HttpClientInterface      $client     The http client
     * @param null|LoggerInterface          $logger     The logger
     *
     * @return iterable
     */
    private static function getDefaultFactories(
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): iterable {
        foreach (self::FACTORY_CLASSES as $factoryClass) {
            if (class_exists($factoryClass)) {
                yield new $factoryClass($dispatcher, $client, $logger);
            }
        }
    }
}
