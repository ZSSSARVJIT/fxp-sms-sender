<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Exception;

use Fxp\Component\SmsSender\Bridge\Amazon\Transport\SnsTransportFactory;
use Fxp\Component\SmsSender\Bridge\Twilio\Transport\TwilioTransportFactory;
use Fxp\Component\SmsSender\Transport\Dsn;

/**
 * Unsupported Host Exception for the SmsSender component.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class UnsupportedHostException extends LogicException
{
    private const HOST_TO_PACKAGE_MAP = [
        'sns' => [
            'class' => SnsTransportFactory::class,
            'package' => 'fxp/amazon-sms-sender',
        ],
        'twilio' => [
            'class' => TwilioTransportFactory::class,
            'package' => 'fxp/twilio-sms-sender',
        ],
    ];

    /**
     * Constructor.
     *
     * @param Dsn $dsn The dsn instance
     */
    public function __construct(Dsn $dsn)
    {
        parent::__construct(static::buildMessage($dsn->getHost(), self::HOST_TO_PACKAGE_MAP));
    }

    /**
     * Build the error message.
     *
     * @param string $host The host
     * @param array  $map  The map
     *
     * @return string
     */
    public static function buildMessage(string $host, array $map): string
    {
        $package = $map[$host] ?? null;

        if (isset($package['class'], $package['package']) && !class_exists($package['class'])) {
            return sprintf(
                'Unable to send sms via "%s" as the bridge is not installed. Try running "composer require %s".',
                $host,
                $package['package']
            );
        }

        return sprintf('The "%s" SMS Sender is not supported.', $host);
    }
}
