<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Events\Listeners;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Listener for exceptions. If Wordpress is available, the WP function `wp_die` will
 * be used to create the response body.
 */
class ExceptionListener
{
    protected LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        $this->logger->error($e->getMessage());

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();
        }

        $content = $e->getMessage();

        // If we have WP, use the `wp_die` function so that any wp_die_handlers can be used.
        // However, we just want to capture the output of the function so that we can use it
        // as the response body.
        if (function_exists('wp_die')) {
            ob_start();
            wp_die($e->getMessage(), 'Error', ['response' => $statusCode, 'exit' => false]);
            $content = ob_get_contents();
            ob_end_clean();
        }

        $response = new Response();
        $response->setStatusCode($statusCode);

        // Always no-cache if server error.
        if ($response->isServerError()) {
            header_remove('cache-control');

            $response->setCache([
                'private' => true,
                'no_cache' => true,
                'must_revalidate' => true,
                'max_age' => 0
            ]);
        }

        $response->setContent($content);
        $event->setResponse($response);
    }
}
