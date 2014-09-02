<?php


namespace Bez\Behat\RestExtension\Client;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SymfonyWebClient
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class SymfonyWebClient implements ClientInterface
{

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
     * @return Response
     */
    public function request(
        $endpoint, $method = 'GET', array $parameters = null, array $headers = array(), array $files = null,
        array $server = null
    ) {
        foreach ($headers as $key => $value) {
            $server['HTTP_' . $key] = $value;
        }
        $this->client->request($method, $endpoint, $parameters, $files, $server);
        return $this->client->getResponse();
    }
}