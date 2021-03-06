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

use Moko\Integrated\InvocationExpectationFailureException,
    Moko\SharedValues as SV;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TestCaseAwareMockDefinitionTest extends \PHPUnit_Framework_TestCase
{
//    public function testVerify_2times()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockInterface'
//        );
//        $chain = $ma->addMethod('doBar', function() {}, 2);
//
//        $this->assertSame($chain, $ma, "Method chaining doesn't work as we expected it to.");
//
//        $mock = $ma->createMock(array(), 'Charlie');
//
//        $isThrown = false;
//        try {
//            $ma->verify();
//        } catch (InvocationExpectationFailureException $e) {
//            $isThrown = true;
//            $this->assertEquals(2, $e->getExpected(), "Moko\_MockInterface::doBar mock was expected to be invoked 2 times.");
//            $this->assertEquals(0, $e->getActual());
//            $this->assertEquals('Charlie', $e->getAliasName(), "Assigned alias name does not match.");
//        }
//        $this->assertTrue($isThrown);
//    }
//
//    public function testVerify_actuallyInvoked2Times()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockInterface'
//        );
//        $ma->addMethod('doBar', function() {}, 2);
//
//        $obj = $ma->createMock();
//
//        $obj->doBar();
//        $obj->doBar();
//
//        $ma->verify();
//    }
//
//    public function testVerify_twoMocksInSameTest()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockInterface'
//        );
//        $ma->addMethod('doBar', function() {}, 2);
//
//        $obj = $ma->createMock();
//
//        $obj->doBar();
//        $obj->doBar();
//
//        $obj2 = $ma->createMock();
//        $obj2->doBar();
//        $obj2->doBar();
//
//        $ma->verify();
//    }
//
//    public function testVerify_zeroInvocationCount()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockInterface'
//        );
//        $ma->addMethod('doBar', function() {}, 0);
//
//        $ma->verify();
//    }
//
//    public function testVerify_delegateMethodWithOneInvocation()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockDelegateClass'
//        );
//        $ma->addDelegateMethod('doFoo', 1);
//
//        /* @var \Moko\_MockDelegateClass $obj */
//        $obj = $ma->createMock();
//
//        $this->assertTrue(
//            $obj instanceof \Moko\_MockDelegateClass,
//            sprintf(
//                'Created mock object is expected to be of type "%s" but actually is "%s".',
//                '\Moko\_MockDelegateClass', get_class($obj)
//            )
//        );
//
//        $obj->doFoo();
//
//        $this->assertEquals(
//            'foo-foo',
//            $obj->foo,
//            'It seems that the "doFoo" parent method was not invoked because this method should have updated Moko\_MockDelegateClass::$foo property'
//        );
//
//        $ma->verify();
//    }
//
//    /**
//     * @expectedException Moko\Integrated\InvocationExpectationFailureException
//     */
//    public function testVerify_delegateMethodExpectationFailure()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockDelegateClass'
//        );
//        $ma->addDelegateMethod('doFoo', 1);
//
//        $obj = $ma->createMock();
//
//        $ma->verify();
//    }
//
//    public function testVerify_withNoExpectationDefinedButInvoked()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockDelegateClass'
//        );
//        $ma->addDelegateMethod('doFoo');
//
//        $obj = $ma->createMock();
//        $obj->doFoo();
//
//        $ma->verify();
//    }
//
//    public function testVerify_withNoExpectationDefinedAndNotInvoked()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockDelegateClass'
//        );
//        $ma->addDelegateMethod('doFoo');
//
//        $obj = $ma->createMock();
//
//        $ma->verify();
//    }
//
//    public function testVerify_ifCountersAreInitialized()
//    {
//        $ma = new TestCaseAwareMockDefinition(
//            $this->getMock(__CLASS__),
//            'Moko\_MockInterface'
//        );
//        $ma->addMethod('doBar', function() {}, 2);
//
//        $obj = $ma->createMock();
//
//        $reflClass = new \ReflectionClass($obj);
//        $invocationCounters = $reflClass->getProperty(SV::INVOCATION_COUNTERS)->getValue(null);
//
//        $methodNames = array_keys($invocationCounters);
//        sort($methodNames);
//
//        $this->assertEquals(
//            $methodNames,
//            array('__construct', 'doBar', 'doBlah', 'doFoo', 'doFooBar'),
//            'Counters must have been initialized to 0.'
//        );
//    }

    public function testCreateMock_ifCountersAreUpdated()
    {
        $ma = new TestCaseAwareMockDefinition(
            $this->getMock(__CLASS__),
            'Moko\_MockInterface'
        );
        $ma->addMethod('doBar', function() {}, 2);
        $ma->addMethod('doBlah', 'foo', 3);

        $obj = $ma->createMock();

        $obj->doBar();

        $obj->doBlah();
        $obj->doBlah();

        $reflClass = new \ReflectionClass($obj);
        $invocationCounters = $reflClass->getProperty(SV::INVOCATION_COUNTERS)->getValue(null);

        $this->assertTrue(isset($invocationCounters['doBar']));
        $this->assertEquals(1, $invocationCounters['doBar'], 'Counter for "doBar" method was not incremented.');

        $this->assertTrue(isset($invocationCounters['doBlah']));
        $this->assertEquals(2, $invocationCounters['doBlah'], 'Counter for "doBlah" method was not incremented.');
    }
}
