<?php


namespace Bez\Behat\RestExtension\Initializer;

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;
use Bez\Behat\RestExtension\Context\ResponseStackAwareInterface;

class ResponseAwareInitializer implements InitializerInterface
{

    /**
     * Checks if initializer supports provided context.
     *
     * @param ContextInterface $context
     *
     * @return Boolean
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof ResponseStackAwareInterface;
    }

    /**
     * Initializes provided context.
     *
     * @param ContextInterface $context
     */
    public function initialize(ContextInterface $context)
    {
        $context->setResponse($response);
    }
}