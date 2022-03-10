<?php

namespace NewsHour\WPCoreThemeComponents;

use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

final class KernelUtilities
{
    private $kernel;
    private $request;

    /**
     * @param KernelInterface $kernel
     * @param Request $request
     */
    private function __construct(KernelInterface $kernel, Request $request)
    {
        $this->kernel = $kernel;
        $this->request = $request;
    }

    /**
     * @param KernelInterface $kernel
     * @param Request $request
     * @return self
     */
    public static function create(KernelInterface $kernel, Request $request): self
    {
        return new KernelUtilities($kernel, $request);
    }

    /**
     * Exits the program on error. If the context is HTTP or WP CLI, the method will exit using wp_die().
     *
     * @param string $message
     * @param string $title
     * @param integer $statusCode
     * @param CoreThemeKernel $kernel
     * @param Request $request
     * @return void
     */
    public function exitOnError($message, $title, $statusCode = 500): void
    {
        $this->kernel->shutdown();

        if (function_exists('wp_die')) {
            $this->kernel->shutdown();
            remove_all_filters('status_header'); // Clear this filter chain so nothing overrides the status code.
            wp_die($message, $title, ['response' => $statusCode]);
        }

        $response = new Response($message, $statusCode);

        if ($response->isServerError()) {
            $response->setCache([
                'no_cache' => true,
                'must_revalidate' => true,
                'max_age' => 0
            ]);
        }

        $response->send();

        $reflector = new ReflectionClass($this->kernel);

        if ($reflector->implementsInterface(TerminableInterface::class)) {
            $this->kernel->terminate($this->request, $response);
        }

        $this->kernel->shutdown();
        exit;
    }
}
