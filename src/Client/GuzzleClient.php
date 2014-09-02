<?php


namespace Bez\Behat\RestExtension\Client;

use Bez\Behat\RestExtension\Client\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Post\PostBodyInterface;
use GuzzleHttp\Query;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

/**
 * Class GuzzleClient
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class GuzzleClient implements ClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    public function __construct(GuzzleClientInterface $client, $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * @param        $endpoint
     * @param string $method
     * @param array  $parameters
     * @param array  $headers
     * @param array  $files
     *
     * @param array  $server
     *
     * @throws \InvalidArgumentException
     * @return Response
     */
    public function request(
        $endpoint, $method = 'GET', array $parameters = null, array $headers = array(), array $files = null,
        array $server = null
    ) {
        $request = $this->client->createRequest($method, $this->url . $endpoint);

        $method = 'prepare' . $method;

        if (!is_callable(array($this, $method))) {
            throw new \InvalidArgumentException(sprintf('Cannot prepare request as %s is not supported.', $method));
        }

        call_user_func_array(array($this, $method), array($request, $parameters, $headers, $files, $server));
        $request->setHeaders($headers);

        $response = $this->client->send($request);

        return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    }

    private function preparePOST(RequestInterface $request, array $parameters = null, array $headers = null, array $files = null, array $server = null)
    {
        /** @var $body PostBodyInterface */
        $body = $request->getBody();
        $body->replaceFields($parameters ?: array());
    }

    private function prepareGET(RequestInterface $request, array $parameters = null, array $headers = null, array $files = null, array $server = null)
    {
        /** @var $query Query */
        $query = $request->getQuery();
        $query->replace($parameters);
    }
}