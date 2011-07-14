<?php
/*
 * Copyright (c) 2011 Sergei Lissovski, http://sergei.lissovski.org
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Moko\Integrated;

use Moko\MockDefinition,
    Moko\Integrated\InvocationExpectationFailureException,
    \Moko\SharedValues as SV;

/**
 * This class hacks PHPUnit_Framework_TestCase class and
 * mimicks interface of PHPUnit's MockObject so the TestRunner
 * treats this class a native one, this allows you to define
 * simple invocation count expectations for methods.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TestCaseAwareMockDefinition extends MockDefinition
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    protected $testCase;

    /**
     * Holds all mock objects that were dispensed by this instance
     * of {@class TestCaseAwareMockDefinition}
     *
     * @var array
     */
    protected $dispensedMocks = array();

    /**
     * @var \ExpectedInvocationCountEvaluatorImpl\Integrated\ExpectedInvocationCountEvaluator
     */
    protected  $expectedInvocationCountEvaluator;

    /**
     * @return \PHPUnit_Framework_TestCase 
     */
    public function getTestCase()
    {
        return $this->testCase;
    }

    /**
     * @param \ExpectedInvocationCountEvaluatorImpl\Integrated\ExpectedInvocationCountEvaluator $expectedInvocationCountEvaluator
     */
    public function setExpectedInvocationCountEvaluator($expectedInvocationCountEvaluator)
    {
        $this->expectedInvocationCountEvaluator = $expectedInvocationCountEvaluator;
    }

    /**
     * @return \ExpectedInvocationCountEvaluatorImpl\Integrated\ExpectedInvocationCountEvaluator
     */
    public function getExpectedInvocationCountEvaluator()
    {
        if (null === $this->expectedInvocationCountEvaluator) {
            $this->expectedInvocationCountEvaluator = new ExpectedInvocationCountEvaluatorImpl();
        }

        return $this->expectedInvocationCountEvaluator;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(\PHPUnit_Framework_TestCase $testCase, $targetName, $omitConstructor = true)
    {
        parent::__construct($targetName, $omitConstructor);

        $this->testCase = $testCase;
        $this->hackTestCase();
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function hackTestCase()
    {
        $reflTc = new \ReflectionClass($this->testCase);
        if (!$reflTc->hasProperty('mockObjects')) {
            throw new \InvalidArgumentException(
                "Provided instance of TestCase seems to be outdated and doesn't have support for native mocking."
            );
        }

        $reflProp = $reflTc->getProperty('mockObjects');
        $reflProp->setAccessible(true);
        $mockObjects = $reflProp->getValue($this->testCase);
        $mockObjects[] = $this;
        $reflProp->setValue($this->testCase, $mockObjects);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Moko\Integrated\TestCaseAwareMockDefinition
     */
    public function addMethod($methodName, $callbackOrReturnValue = null, $expectedInvocationCount = null)
    {
        $chain = parent::addMethod($methodName, $callbackOrReturnValue);

        $this->definitions[$methodName]['expectedInvocationsCount'] = $expectedInvocationCount;

        return $chain;
    }

    /**
     * {@inheritdoc}
     */
    public function addDelegateMethod($methodName, $expectedInvocationCount = null)
    {
        $chain = parent::addDelegateMethod($methodName);

        $this->definitions[$methodName]['expectedInvocationsCount'] = $expectedInvocationCount;

        return $chain;
    }

    /**
     * {@inheritdoc}
     */
    public function createMock(array $constructorParams = array(), $aliasName = null, $suppressUnexpectedInteractionExceptions = false)
    {
        $mock = parent::createMock($constructorParams, $aliasName, $suppressUnexpectedInteractionExceptions);
        $this->dispensedMocks[] = $mock;

        $this->initInvocationCounters($mock);
        
        return $mock;
    }

    /**
     * Will initialize the invocation counters for all methods to 0.
     */
    private function initInvocationCounters($mock)
    {
        $invocationCounters = array();

        $reflClass = new \ReflectionClass($mock);
        foreach ($reflClass->getMethods() as $reflMethod) {
            $invocationCounters[$reflMethod->getName()] = 0;
        }

        $reflClass->getProperty(SV::INVOCATION_COUNTERS)->setValue(null, $invocationCounters);
    }

    /**
     * Verifies if methods were invoked declared number of times and if not
     * the {@class Moko\Integrated\InvocationExpectationFailureException} is thrown.
     *
     * @throws InvocationExpectationFailureException
     */
    public function verify()
    {
        foreach ($this->dispensedMocks as $mock) {
            $reflMock = new \ReflectionClass($mock);

            $invocationCounters = $reflMock->getProperty(SV::INVOCATION_COUNTERS)->getValue(null);
            foreach (get_class_methods($mock) as $methodName) {
                if (isset($this->definitions[$methodName])) {
                    $def = $this->definitions[$methodName];

                    // if expectation is NULL treating it we just didn't want to specify any
                    if ($def['expectedInvocationsCount'] === null) {
                        continue;
                    }

                    $aliasName = $reflMock->getProperty(SV::ALIAS_NAME)->getValue(null);
                    $this->getExpectedInvocationCountEvaluator()->evaluate(
                        $this->targetName,
                        $mock,
                        $methodName,
                        $aliasName,
                        $invocationCounters,
                        $def['expectedInvocationsCount']
                    );
                }
            }
        }
    }



    // mimicking the phpUnit's MockObject's interface

    public function __phpunit_verify()
    {
        $this->verify();
    }

    /**
     * Clears static counters of method invocations in known mock objects,
     * this allows you sharing a mock in your test methods.
     */
    public function __phpunit_cleanup()
    {
        foreach ($this->dispensedMocks as $mock) {
            $reflMock = new \ReflectionClass($mock);
            $reflMock->getProperty(SV::INVOCATION_COUNTERS)->setValue(null, array());
        }
    }
}
