<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Event;

use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\Result;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MessageResultEvent extends AbstractMessageEvent
{
    /**
     * @var Result
     */
    private $result;

    /**
     * Constructor.
     *
     * @param RawMessage  $message
     * @param SmsEnvelope $envelope
     * @param Result      $result
     */
    public function __construct(RawMessage $message, SmsEnvelope $envelope, Result $result)
    {
        parent::__construct($message, $envelope);

        $this->result = $result;
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
