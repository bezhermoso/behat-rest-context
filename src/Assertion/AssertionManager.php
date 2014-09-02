<?php


namespace Bez\Behat\RestExtension\Assertion;

class AssertionManager
{
    /**
     * @var AssertionBuilderInterface[]
     */
    protected $builders = array();

    protected $assertionCache = array();

    protected $checkerCache = array();

    public function register(AssertionBuilderInterface $assertion)
    {
        $this->builders[] = $assertion;
    }

    /**
     * @param $statement
     *
     * @return callable
     */
    public function getAssertion($statement)
    {
        if (isset($this->assertionCache[$statement])) {
            return $this->assertionCache;
        }

        $this->assertionCache[$statement] = null;

        foreach ($this->builders as $assertion) {
            if ($assertion->supports($statement)) {
                $this->assertionCache[$statement] = $assertion->createAssertion($statement);
            }
        }

        return $this->assertionCache[$statement];
    }

    public function getChecker($statement)
    {
        if (isset($this->checkerCache[$statement])) {
            return $this->checkerCache[$statement];
        }

        $this->checkerCache[$statement] = null;

        foreach ($this->builders as $assertion) {
            if ($assertion->supports($statement)) {
                $this->checkerCache[$statement] = $assertion->createChecker($statement);
            }
        }

        return $this->checkerCache[$statement];
    }
}