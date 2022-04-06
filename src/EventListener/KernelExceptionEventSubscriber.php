<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\SuiteNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class KernelExceptionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<class-string, array<mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => [
                ['onKernelException', 100],
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof MethodNotAllowedHttpException) {
            $event->setResponse(new Response(null, 405));
            $event->stopPropagation();
        }

        if ($throwable instanceof SuiteNotFoundException) {
            $statusCode = 'DELETE' === $event->getRequest()->getMethod() ? 200 : 404;

            $event->setResponse(new Response(null, $statusCode));
            $event->allowCustomResponseCode();
            $event->stopPropagation();
        }
    }
}
