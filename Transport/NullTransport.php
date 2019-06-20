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

use Fxp\Component\SmsSender\SentMessage;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class NullTransport extends AbstractTransport
{
    /**
     * {@inheritdoc}
     */
    protected function doSend(SentMessage $message): void
    {
    }
}
