<?php


namespace Bez\Behat\RestExtension\Context;

use Bez\Behat\RestExtension\ResponseStack;

/**
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
interface ResponseStackAwareInterface
{
    public function setResponseStack(ResponseStack $response);
}
