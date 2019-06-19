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

/**
 * Result of the transport.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Result
{
    /**
     * @var string
     */
    private $transportClassName;

    /**
     * @var SuccessResult[]
     */
    private $successes = [];

    /**
     * @var ErrorResult[]
     */
    private $errors = [];

    /**
     * Constructor.
     *
     * @param string $transportClassName
     */
    public function __construct(string $transportClassName)
    {
        $this->transportClassName = $transportClassName;
    }

    public function getTransportClassName(): string
    {
        return $this->transportClassName;
    }

    public function add(AbstractResultItem $result): void
    {
        if ($result instanceof SuccessResult) {
            $this->successes[] = $result;
        } elseif ($result instanceof ErrorResult) {
            $this->errors[] = $result;
        }
    }

    /**
     * @return SuccessResult[]
     */
    public function getSuccesses(): array
    {
        return $this->successes;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @return ErrorResult[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
