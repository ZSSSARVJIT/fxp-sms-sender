<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Mime;

use Symfony\Component\Mime\Header\HeaderInterface;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\TextPart;

/**
 * Sms message.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Sms extends Message
{
    /**
     * @param Phone|string $phone
     *
     * @return static
     */
    public function from($phone): self
    {
        return $this->setListPhoneHeaderBody('From', [$phone]);
    }

    /**
     * @return static
     */
    public function optionalFrom(): self
    {
        return $this->from('+100');
    }

    /**
     * @return null|Phone
     */
    public function getFrom(): ?Phone
    {
        return $this->getPhoneFromListHeader('From');
    }

    /**
     * @param Phone|Phone[]|string|string[] $phones
     *
     * @return static
     */
    public function addTo(...$phones): self
    {
        return $this->addListPhoneHeaderBody('To', $phones);
    }

    /**
     * @param Phone|Phone[]|string|string[] $phones
     *
     * @return static
     */
    public function to(...$phones): self
    {
        return $this->setListPhoneHeaderBody('To', $phones);
    }

    /**
     * @return Phone[]
     */
    public function getTo(): array
    {
        return $this->getPhonesFromListHeader('To');
    }

    /**
     * @param string $body
     *
     * @return static
     */
    public function text(string $body): self
    {
        $this->setBody(new TextPart($body));

        return $this;
    }

    public function getText(): ?string
    {
        $body = $this->getBody();

        return $body instanceof TextPart ? $body->getBody() : null;
    }

    /**
     * @param HeaderInterface $header
     *
     * @return static
     */
    public function addHeader(HeaderInterface $header): self
    {
        $this->getHeaders()->add($header);

        return $this;
    }

    /**
     * @param string           $name
     * @param Phone[]|string[] $phones
     *
     * @return static
     */
    private function addListPhoneHeaderBody(string $name, array $phones): self
    {
        /** @var null|MailboxListHeader $to */
        if (null === $to = $this->getHeaders()->get($name)) {
            return $this->setListPhoneHeaderBody($name, $phones);
        }

        $to->addAddresses(Phone::createArray($phones));

        return $this;
    }

    /**
     * @param string           $name
     * @param Phone[]|string[] $phones
     *
     * @return static
     */
    private function setListPhoneHeaderBody(string $name, array $phones): self
    {
        $phones = Phone::createArray($phones);
        $headers = $this->getHeaders();

        /** @var null|MailboxListHeader $to */
        if (null !== $to = $headers->get($name)) {
            $to->setAddresses($phones);
        } else {
            $headers->addMailboxListHeader($name, $phones);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Phone[]
     */
    private function getPhonesFromListHeader(string $name): array
    {
        $header = $this->getHeaders()->get($name);
        $phones = [];

        if ($header instanceof MailboxListHeader
                && null !== ($body = $header->getBody())
                && \count($body) > 0) {
            foreach ($body as $value) {
                if ($value instanceof Phone) {
                    $phones[] = $value;
                }
            }
        }

        return $phones;
    }

    private function getPhoneFromListHeader(string $name): ?Phone
    {
        $phones = $this->getPhonesFromListHeader($name);

        return \count($phones) > 0 ? $phones[0] : null;
    }
}
