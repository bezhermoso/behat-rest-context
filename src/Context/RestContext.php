<?php


namespace Bez\Behat\RestExtension\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Bez\Behat\RestExtension\Assertion\AssertionManager;
use Bez\Behat\RestExtension\Client\ClientInterface;
use Bez\Behat\RestExtension\ResponseStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Parser;

/**
 * Class RestContext
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class RestContext extends BehatContext implements ResponseStackAwareInterface
{
    /**
     * @var ResponseStack
     */
    private $responseStack;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    private $assertionManager;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->assertionManager = new AssertionManager();
    }

    /**
     * @param ResponseStack $response
     */
    public function setResponseStack(ResponseStack $response)
    {
        $this->responseStack = $response;
    }

    private function convertToParameters(TableNode $table)
    {
        $parser   = new Parser();
        $parameters = array();

        foreach ($table->getHash() as $row) {
            $parameters[$row['KEY']] = $parser->parse($row['VALUE']);
        }

        return $parameters;
    }

    /**
     * @When /the (?<endpoint>.*) endpoint is called(?: with the following(?:\s(?<method>GET|POST|PUT))? data:)/
     */
    public function whenEndpointIsCalled($endpoint, $method, TableNode $table)
    {
        $parameters = $this->convertToParameters($table);
        $this->responseStack->setLastResponse($this->client->request($endpoint, $method, $parameters));
    }

    /**
     * @Then /the status code should be (\d+){3,3}/
     */
    public function thenAssertStatusCode($code)
    {
        $response = $this->getLastResponse();
        if (!$response->getStatusCode() !== (int) $code) {
            throw new \Exception(sprintf('Actual status code is %d', $response->getStatusCode()));
        }
    }

    /**
     * @Then /^(\[.*\]) should(?:\sbe)? (.*)$/
     */
    public function assertStatement($propertyPath, $statement)
    {
        $assertions = $this->assertionManager->getSupportingAssertions($statement);
        if (count($assertions) === 0) {
            throw new PendingException(sprintf('%s cannot be asserted.', $statement));
        }

        foreach ($assertions as $assertion) {
            $assertion->assert($this->accessor->getValue($this->getResponseData(), $propertyPath), $statement);
        }
    }

    /**
     * @Then /^(all|none|\d+|less than \d+|more than \d+) of (\[.*\]) should(?:\sbe)? (.*)$/
     */
    public function assertStatementCollection($qualifier, $propertyPath, $statement)
    {
        $values = $this->accessor->getValue($this->getResponseData(), $propertyPath);

        if (!is_array($values)) {
            throw new \RuntimeException('Cannot assert statement on a non-array.');
        }

        $checker = $this->assertionManager->getChecker($statement);

        if ($checker === null) {
            throw new PendingException(sprintf('%s cannot be asserted.', $statement));
        }

        if ($qualifier === 'all') {
            foreach ($values as $value) {
                if ($checker($value) === false) {
                    throw new \Exception();
                }
            }
            return;
        }

        if ($qualifier === 'none') {
            foreach ($values as $value) {
                if ($checker($value) === true) {
                    throw new \Exception();
                }
            }
            return;
        }

        $matches = array();

        if (preg_match_all('/^more than (?<count>\d+)$/', $qualifier, $matches)) {

            $satisfied = 0;
            $min = 1 + (int) $matches['count'][0];
            foreach ($values as $value) {
                if ($checker($value)) {
                    $satisfied++;
                }
                if ($satisfied === $min) {
                    return;
                }
            }
            throw new \Exception(sprintf('Only %d satisfied the condition.', $statement));

        } elseif (preg_match_all('/^less than (?<count>\d+)$/', $qualifier, $matches)) {

            $satisfied = 0;
            $max = -1 + (int) $matches['count'][0];
            foreach ($values as $value) {
                if ($checker($value)) {
                    $satisfied++;
                }
                if ($satisfied === $max) {
                    throw new \Exception(sprintf('%d satisfied the condition.', $statement));
                }
            }

            return;
        }

        $satisfied = 0;
        $target = (int) $matches['count'][0];
        foreach ($values as $value) {
            if ($checker($value)) {
                $satisfied++;
            }
            if ($satisfied > $target) {
                throw new \Exception(sprintf('%d satisfied the condition.', $statement));
            }
        }
        if ($satisfied < $target) {
            throw new \Exception(sprintf('Only %d satisfied the condition.', $statement));
        }
    }

    /**
     * Guesses the format of the response body and returns a decoded version if applicable.
     *
     * @return mixed|string
     */
    public function getResponseData()
    {
        $response = $this->getLastResponse();

        if ($response->headers->get('content-type') === 'application/json') {
            return json_decode($response->getContent(), true);
        } else {
            return $response->getContent();
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLastResponse()
    {
        return $this->responseStack->getLastResponse();
    }
}
