<?php


namespace Bez\Behat\RestExtension;

use Symfony\Component\HttpFoundation\Response;

class ResponseStack
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Response $response
     */
    public function setLastResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     * @throws \RuntimeException
     */
    public function getLastResponse()
    {
        if ($this->response === null) {
            throw new \RuntimeException('REST API not called yet.');
        }

        return $this->response;
    }
} 