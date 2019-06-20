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

use Fxp\Component\SmsSender\Bridge\Amazon;
use Fxp\Component\SmsSender\Bridge\Twilio;
use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Exception\LogicException;
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
    public static function fromDsn(
        string $dsn,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        // failover?
        $dsns = preg_split('/\s++\|\|\s++/', $dsn);

        if (\count($dsns) > 1) {
            $transports = [];

            foreach ($dsns as $sDsn) {
                $transports[] = self::createTransport($sDsn, $dispatcher, $client, $logger);
            }

            return new Transport\FailoverTransport($transports);
        }

        // round robin?
        $dsns = preg_split('/\s++&&\s++/', $dsn);

        if (\count($dsns) > 1) {
            $transports = [];

            foreach ($dsns as $sDsn) {
                $transports[] = self::createTransport($sDsn, $dispatcher, $client, $logger);
            }

            return new Transport\RoundRobinTransport($transports);
        }

        return self::createTransport($dsn, $dispatcher, $client, $logger);
    }

    /**
     * Create the transport from the DSN.
     *
     * @param string                        $dsn        The DSN to build the transport
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     * @param null|HttpClientInterface      $client     The custom http client
     *
     * @return TransportInterface
     */
    protected static function createTransport(
        string $dsn,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        if (false === $parsedDsn = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN is invalid.', $dsn));
        }

        if (!isset($parsedDsn['host'])) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN must contain a sender name.', $dsn));
        }

        $method = 'create'.ucfirst($parsedDsn['host']).'Transport';

        if (method_exists(static::class, $method)) {
            $call = static::class.'::'.$method;

            return $call($parsedDsn, $dispatcher, $logger, $client);
        }

        throw new LogicException(sprintf('The "%s" SMS Sender is not supported.', $parsedDsn['host']));
    }

    /**
     * Create the Null transport.
     *
     * @param array                         $parsedDsn  The parsed dsn
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     *
     * @return TransportInterface
     */
    protected static function createNullTransport(
        array $parsedDsn,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        if ('sms' !== $parsedDsn['scheme']) {
            throw new LogicException(sprintf('The "%s" scheme is not supported for SMS Sender "%s".', $parsedDsn['scheme'], $parsedDsn['host']));
        }

        return new Transport\NullTransport($dispatcher, $logger);
    }

    /**
     * Create the Amazon SNS transport.
     *
     * @param array                         $parsedDsn  The parsed dsn
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     * @param null|HttpClientInterface      $client     The custom http client
     *
     * @return TransportInterface
     */
    protected static function createSnsTransport(
        array $parsedDsn,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null,
        HttpClientInterface $client = null
    ): TransportInterface {
        TransportUtil::validateInstall(Amazon\SmsTransport::class, 'Amazon SNS', 'fxp/amazon-sms-sender');
        parse_str($parsedDsn['query'] ?? '', $query);

        return new Amazon\SmsTransport(
            urldecode($parsedDsn['user'] ?? ''),
            urldecode($parsedDsn['pass'] ?? ''),
            $query['region'] ?? null,
            $query['sender_id'] ?? null,
            $query['type'] ?? null,
            $dispatcher,
            $client,
            $logger
        );
    }

    /**
     * Create the Twilio transport.
     *
     * @param array                         $parsedDsn  The parsed dsn
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     * @param null|HttpClientInterface      $client     The custom http client
     *
     * @return TransportInterface
     */
    protected static function createTwilioTransport(
        array $parsedDsn,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null,
        HttpClientInterface $client = null
    ): TransportInterface {
        TransportUtil::validateInstall(Twilio\SmsTransport::class, 'Twilio', 'fxp/twilio-sms-sender');
        parse_str($parsedDsn['query'] ?? '', $query);

        return new Twilio\SmsTransport(
            urldecode($parsedDsn['user'] ?? ''),
            urldecode($parsedDsn['pass'] ?? ''),
            $query['accountSid'] ?? null,
            $query['region'] ?? null,
            $dispatcher,
            $client,
            $logger
        );
    }
}
