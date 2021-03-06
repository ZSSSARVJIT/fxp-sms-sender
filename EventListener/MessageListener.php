<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\EventListener;

use Fxp\Component\SmsSender\Event\MessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Message;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MessageListener implements EventSubscriberInterface
{
    /**
     * @var null|Headers
     */
    private $headers;

    /**
     * @var null|BodyRendererInterface
     */
    private $renderer;

    /**
     * Constructor.
     *
     * @param null|Headers               $headers  The headers
     * @param null|BodyRendererInterface $renderer The body renderer
     */
    public function __construct(?Headers $headers = null, ?BodyRendererInterface $renderer = null)
    {
        $this->headers = $headers;
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    /**
     * Action on message event.
     *
     * @param MessageEvent $event The event
     */
    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!$message instanceof Message) {
            return;
        }

        $this->setHeaders($message);
        $this->renderMessage($message);
    }

    /**
     * Set the headers.
     *
     * @param Message $message The message
     */
    private function setHeaders(Message $message): void
    {
        if (!$this->headers) {
            return;
        }

        $headers = $message->getHeaders();

        foreach ($this->headers->all() as $name => $header) {
            if (!$headers->has($name)) {
                $headers->add($header);
            } elseif (!Headers::isUniqueHeader($name)) {
                $headers->add($header);
            }
        }
    }

    /**
     * Render the message.
     *
     * @param Message $message The message
     */
    private function renderMessage(Message $message): void
    {
        if (!$this->renderer) {
            return;
        }

        $this->renderer->render($message);
    }
}
