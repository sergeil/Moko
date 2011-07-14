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

require_once __DIR__.'/../_mocks.php';

require_once __DIR__.'/../../bootstrap.php';

use Moko\Integrated\InvocationExpectationFailureException;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ExpectedInvocationCountEvaluatorImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\Integrated\ExpectedInvocationCountEvaluatorImpl
     */
    protected $eice;

    public function setUp()
    {
        $this->eice = new ExpectedInvocationCountEvaluatorImpl();
    }

    public function tearDown()
    {
        $this->eice = null;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEvaluate_notIntegerExpectedCount()
    {
        $this->eice->evaluate(null, null, null, null, array(), 'foo');
    }

    public function testEvaluate_expectationsAreSatisfied()
    {
        $invocationCounters = array(
            'fooMethod' => 2
        );
        $this->eice->evaluate('target', 'mock', 'fooMethod', 'aliasName', $invocationCounters, 2);
    }

    public function testEvaluate_expectationAreNotSatisfied()
    {
        $isThrown = false;
        $invocationCounters = array(
            'fooMethod' => 2
        );

        try {
            $this->eice->evaluate('FooClazz', 'an-instance', 'fooMethod', 'foo-alias', $invocationCounters, 3);
        } catch (InvocationExpectationFailureException $e) {
            $isThrown = true;

            $this->assertEquals('foo-alias', $e->getAliasName(), "Original alias name doesn't with one that is found in exception.");
            $this->assertEquals(3, $e->getExpected(), "Original expected invocation count doesn't with one that is found in exception.");
            $this->assertEquals(2, $e->getActual(), "Original actual invocation count doesn't with one that is found in exception.");
        }

        $this->assertTrue($isThrown, 'An exception must have been thrown when invocation expectations are not satisfied.');
    }


}
