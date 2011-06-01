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

namespace Moko;

require_once '_mocks.php';

require_once '../../src/Moko/ClassLoader.php';
ClassLoader::register();

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TestCaseAwareMockDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testVerify_2times()
    {
        $ma = new TestCaseAwareMockDefinition(
            $this->getMock(__CLASS__),
            'Moko\_MockInterface'
        );
        $ma->addMethod('doBar', function() {}, 2);

        $mock = $ma->createMock();

        $isThrown = false;
        try {
            $ma->verify();
        } catch (InvocationExpectationFailureException $e) {
            $isThrown = true;
            $this->assertEquals(2, $e->getExpected());
            $this->assertEquals(0, $e->getActual());
        }
        $this->assertTrue($isThrown);
    }

    public function testVerify_actuallyInvoked2Times()
    {
        $ma = new TestCaseAwareMockDefinition($this, 'Moko\_MockInterface');
        $ma->addMethod('doBar', function() {}, 2);

        $obj = $ma->createMock();

        $obj->doBar();
        $obj->doBar();

        $ma->verify();
    }

    public function testVerify_twoMocksInSameTest()
    {
        $ma = new TestCaseAwareMockDefinition($this, 'Moko\_MockInterface');
        $ma->addMethod('doBar', function() {}, 2);

        $obj = $ma->createMock();

        $obj->doBar();
        $obj->doBar();

        $obj2 = $ma->createMock();
        $obj2->doBar();
        $obj2->doBar();

        $ma->verify();
    }
    
}
