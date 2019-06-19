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

use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Exception\TransportException;
use Fxp\Component\SmsSender\Exception\TransportResultException;
use Fxp\Component\SmsSender\Mime\Sms;
use Fxp\Component\SmsSender\SentMessage;
use Fxp\Component\SmsSender\SmsEnvelope;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Abstract class for the api transport.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractApiTransport extends AbstractTransport
{
    /**
     * @var null|HttpClientInterface
     */
    protected $client;

    public function __construct(
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);

        $this->client = $client;
    }

    protected function doSend(SentMessage $message): void
    {
        try {
            $sms = $message->getOriginalMessage();

            if (!$sms instanceof Sms) {
                throw new InvalidArgumentException(sprintf('The message must be an instance %s ("%s" given).', Sms::class, \get_class($sms)));
            }
        } catch (\Exception $e) {
            throw new TransportException(sprintf('Unable to send message with the "%s" transport: %s', \get_class($this), $e->getMessage()), 0, $e);
        }

        $this->doSendSms($sms, $message->getEnvelope(), $message->getResult());

        if ($message->getResult()->hasErrors()) {
            throw new TransportResultException($message->getResult());
        }
    }

    abstract protected function doSendSms(Sms $sms, SmsEnvelope $envelope, Result $result): void;
}
