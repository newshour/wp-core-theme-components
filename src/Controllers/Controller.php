<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Controllers;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Timber\Loader;
use Timber\Timber;
use NewsHour\WPCoreThemeComponents\Utilities;
use NewsHour\WPCoreThemeComponents\Contexts\Context;

/**
 * The parent Controller class.
 *
 * @abstract
 */
abstract class Controller implements ServiceSubscriberInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }

    /**
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        return [];
    }

    /**
     * Get the Response object used build the final response.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        if (empty($this->response)) {
            $this->response = new Response();
        }

        return $this->response;
    }

    /**
     * Renders the view as HTML and returns a Response object. Timber template caching parameters
     * can be passed via the $kwargs argument.
     *
     * kwargs: (string) cache_mode, (int) expires, (array) headers, (int) status_code
     *
     * @param string $template
     * @param Context $context
     * @param array $kwargs
     * @return Response|null
     */
    protected function render(string $template, Context $context, array $kwargs = []): ?Response
    {
        try {
            $response = $this->getResponse();

            if ($response->isNotModified($context->getRequest())) {
                return $response;
            }

            $expires = isset($kwargs['expires']) && $kwargs['expires'] > -1 ? $kwargs['expires'] : false;
            $cacheMode = empty($kwargs['cache_mode']) ? Loader::CACHE_USE_DEFAULT : $kwargs['cache_mode'];
            $statusCode = empty($kwargs['status_code']) ? http_response_code() : $kwargs['status_code'];

            $headers = array_merge(
                $this->getQueuedHeaders(),
                empty($kwargs['headers']) ? [] : $kwargs['headers']
            );

            $content = Timber::fetch(
                $template,
                $context->toArray(),
                $expires,
                $cacheMode
            );

            if ($content === false) {
                trigger_error(
                    sprintf(
                        // phpcs:ignore
                        'The template "%s" could not be rendered. Please make sure the template exists and is a valid Twig file.',
                        $template
                    ),
                    E_USER_ERROR
                );
            }

            if (!Utilities::hasKey('Content-Type', $headers)) {
                $headers['Content-Type'] = 'text/html; charset=' . get_option('blog_charset');
            }

            // Build the response.
            $response->setContent($content);
            $response->setStatusCode($statusCode);
            $response->headers->add($headers);
            $response->prepare($context->getRequest());

            return $response;
        } catch (InvalidArgumentException $iae) {
            trigger_error($iae);
        }
    }

    /**
     * Renders a file for download.
     *
     * @param mixed $file
     * @param string $filename Optional
     * @param string $contentDisposition Optional, default is attachment.
     * @return BinaryFileResponse|null
     */
    public function renderFile($file, $filename = '', $contentDisposition = 'attachment'): ?BinaryFileResponse
    {
        if (!is_file($file)) {
            wp_die('File Not Found', 'File Not Found', ['response' => 404]);
            exit;
        }

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($contentDisposition, empty($filename) ? basename($file) : $filename);

        return $response;
    }

    /**
     * Renders the view as JSON and returns a Response object.
     *
     * kwargs: (int) json_encode_options, (array) headers, (int) status_code
     *
     * @param array $data
     * @param Context $context
     * @param array $kwargs
     * @return Response|null
     */
    protected function renderJson(array $data, Context $context, array $kwargs = []): ?Response
    {
        $response = $this->getResponse();

        if ($response->isNotModified($context->getRequest())) {
            return $response;
        }

        $options = empty($kwargs['json_encode_options']) ? 0 : $kwargs['json_encode_options'];
        $statusCode = empty($kwargs['status_code']) ? http_response_code() : $kwargs['status_code'];
        $headers = empty($kwargs['headers']) ? [] : $kwargs['headers'];

        // Add CORS headers.
        $this->addCorsHeaders($context->getRequest(), $headers);

        if (!Utilities::hasKey('Content-Type', $headers)) {
            $headers['Content-Type'] = 'application/json; charset=' . get_option('blog_charset');
        }

        try {
            // Build the JSON.
            $content = wp_json_encode($data, (int)$options);

            if ($content === false) {
                $content = json_encode(['message' => 'Data could not be encoded as JSON.']);
                $statusCode = 400;
            }

            // Build the response.
            $response->setContent($content);
            $response->setStatusCode($statusCode);
            $response->headers->add($headers);
            $response->prepare($context->getRequest());

            return $response;
        } catch (InvalidArgumentException $iae) {
            trigger_error($iae);
        }
    }

    /**
     * Renders a string and returns a Response object.
     *
     * @param string $str
     * @param array $kwargs
     * @return Response|null
     */
    public function renderString(string $str, array $kwargs = []): ?Response
    {
        $statusCode = empty($kwargs['status_code']) ? http_response_code() : $kwargs['status_code'];

        $headers = array_merge(
            $this->getQueuedHeaders(),
            empty($kwargs['headers']) ? [] : $kwargs['headers']
        );

        if (!Utilities::hasKey('Content-Type', $headers)) {
            $headers['Content-Type'] = 'text/plain';

            if (strtolower(substr($str, 0, 4)) == '<html') {
                $headers['Content-Type'] = 'text/html; charset=' . get_option('blog_charset');
            }
        }

        try {
            // Build the response.
            $response = $this->getResponse();
            $response->setContent($str);
            $response->setStatusCode($statusCode);
            $response->headers->add($headers);

            return $response;
        } catch (InvalidArgumentException $iae) {
            trigger_error($iae);
        }
    }

    /**
     * Redirect to a URL. Call the internal Wordpress function 'wp_safe_redirect'.
     *
     * @param string $url
     * @param integer $statusCode
     * @return void
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        wp_safe_redirect($url, $statusCode, '');
        exit;
    }

    /**
     * Add CORS headers to the headers array.
     *
     * @param Request $request
     * @param array $headers
     * @return void
     */
    private function addCorsHeaders(Request $request, array &$headers): void
    {
        $origins = get_allowed_http_origins();

        if (!is_array($origins)) {
            return;
        }

        $origins = array_map('trim', array_unique($origins));
        $clientOrigin = $request->headers->get('Origin');
        $allowed = '';

        if (in_array('*', $origins)) {
            $allowed = '*';
        } elseif (!is_null($clientOrigin) && in_array($clientOrigin, $origins)) {
            $allowed = $clientOrigin;
        }

        if (!empty($allowed)) {
            $headers['Vary'] = 'Origin';
            $headers['Access-Control-Allow-Origin'] = $allowed;
        }
    }

    /**
     * Retrieves the headers set by Wordpress so that they can be into our
     * Response object.
     *
     * @return array
     */
    private function getQueuedHeaders(): array
    {
        $queued = headers_list();
        $keyed = [];

        foreach ($queued as $header) {
            if (is_array($split = HeaderUtils::split($header, ':'))) {
                $keyed[$split[0]] = isset($split[1]) ? $split[1] : '';
                header_remove($split[0]);
            }
        }

        return $keyed;
    }
}
