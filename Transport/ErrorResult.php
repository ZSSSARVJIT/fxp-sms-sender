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

use Fxp\Component\SmsSender\Mime\Phone;

/**
 * Success result of the transport.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ErrorResult extends AbstractResultItem
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $code;

    /**
     * @var null|\Exception
     */
    private $exception;

    public function __construct(
        Phone $recipient,
        string $message,
        string $code,
        array $data = [],
        ?\Exception $exception = null
    ) {
        parent::__construct($recipient, $data);

        $this->message = $message;
        $this->code = $code;
        $this->exception = $exception;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getException(): ?\Exception
    {
        return $this->exception;
    }
}
