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

use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Exception\LogicException;
use Fxp\Component\SmsSender\Transport\FailoverTransport;
use Fxp\Component\SmsSender\Transport\RoundRobinTransport;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Transport
{
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
    public static function fromDsn(string $dsn, EventDispatcherInterface $dispatcher = null, HttpClientInterface $client = null, LoggerInterface $logger = null): TransportInterface
    {
        // failover?
        $dsns = preg_split('/\s++\|\|\s++/', $dsn);

        if (\count($dsns) > 1) {
            $transports = [];

            foreach ($dsns as $sDsn) {
                $transports[] = self::createTransport($sDsn, $dispatcher, $client, $logger);
            }

            return new FailoverTransport($transports);
        }

        // round robin?
        $dsns = preg_split('/\s++&&\s++/', $dsn);

        if (\count($dsns) > 1) {
            $transports = [];

            foreach ($dsns as $sDsn) {
                $transports[] = self::createTransport($sDsn, $dispatcher, $client, $logger);
            }

            return new RoundRobinTransport($transports);
        }

        return self::createTransport($dsn, $dispatcher, $client, $logger);
    }

    /**
     * Create the transport from the DSN.
     *
     * @param string                        $dsn        The DSN to build the transport
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|HttpClientInterface      $client     The custom http client
     * @param null|LoggerInterface          $logger     The logger
     *
     * @return TransportInterface
     */
    private static function createTransport(string $dsn, EventDispatcherInterface $dispatcher = null, HttpClientInterface $client = null, LoggerInterface $logger = null): TransportInterface
    {
        if (false === $parsedDsn = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN is invalid.', $dsn));
        }

        if (!isset($parsedDsn['host'])) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN must contain a sender name.', $dsn));
        }

        $user = urldecode($parsedDsn['user'] ?? '');
        $pass = urldecode($parsedDsn['pass'] ?? '');
        parse_str($parsedDsn['query'] ?? '', $query);

        if ('null' === $parsedDsn['host']) {
            if ('sms' !== $parsedDsn['scheme']) {
                throw new LogicException(sprintf('The "%s" scheme is not supported for SMS Sender "%s".', $parsedDsn['scheme'], $parsedDsn['host']));
            }

            return new Transport\NullTransport($dispatcher, $logger);
        }

        throw new LogicException(sprintf('The "%s" SMS Sender is not supported.', $parsedDsn['host']));
    }
}
