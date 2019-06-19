<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Messenger;

use Fxp\Component\SmsSender\Transport\TransportInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MessageHandler
{
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function __invoke(SendSmsMessage $message): void
    {
        $this->transport->send($message->getMessage(), $message->getEnvelope());
    }
}
