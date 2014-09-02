<?php


namespace Bez\Behat\RestExtension\Assertion;

/**
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
interface AssertionBuilderInterface
{
    public function supports($statement);

    public function createAssertion($statement);

    public function createChecker($statement);
}
