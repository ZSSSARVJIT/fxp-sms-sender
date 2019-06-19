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
 * Abstract result item of the transport.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractResultItem
{
    /**
     * @var Phone
     */
    protected $recipient;

    /**
     * @var array
     */
    protected $data;

    public function __construct(Phone $recipient, array $data)
    {
        $this->recipient = $recipient;
        $this->data = $data;
    }

    public function getRecipient(): Phone
    {
        return $this->recipient;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
