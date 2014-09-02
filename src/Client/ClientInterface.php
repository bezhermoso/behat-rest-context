<?php

namespace Bez\Behat\RestExtension\Client;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface ClientInterface
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
interface ClientInterface
{
    /**
     * @param        $endpoint
     * @param string $method
     * @param array  $parameters
     * @param array  $headers
     * @param array  $files
     *
     * @param array  $server
     *
     * @return Response
     */
    public function request($endpoint, $method = 'GET', array $parameters = null, array $headers = array(), array $files = null, array $server = null);
}