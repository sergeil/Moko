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

namespace Moko\Sandbox;

require_once __DIR__.'/bootstrap.php';


interface MyInterface
{
    public function method1($param1, array $param2 = array(1, 2, 3), \stdClass $stdClass = null);
}

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class PhpUnitIntegration extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    private $mf;

    public function setUp()
    {
        /*
         * If in your test case you are planning to generate several mocks
         * then it is reasonable to use \Moko\MockFactory. It is going
         * to be especially useful when it want to use \Moko\TestCaseAwareMockDefinition.
         */
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->mf = null;
    }

    public function test_multipleMockInOneTest()
    {
        /**
         * If for some reason you don't want to use factory, this line could be replace with:
         * $md = new \Moko\TestCaseAwareMockDefinition($this, 'Moko\Sandbox\MyInterface');
         */
        $md = $this->mf->createTestCaseAware('Moko\Sandbox\MyInterface');

        /**
         * In first parameter of provided closure instance of mock
         * object will be injected. In case the method you're mocking
         * is static then mock's FQCN will be used instead.
         */
        $tc = $this;
        $md->addMethod('method1', function($self) use($tc) {
            $tc->assertTrue($self instanceof MyInterface);

            return 'done';
        }, 2);

        $obj = $md->createMock();

        /*
         * Method execution results are not lost but rather returned to a caller.
         * Moko is capable to mimick sophisticated method signatures.
         */
        $this->assertEquals('done', $obj->method1(null, array(), new \stdClass()));

        /*
         * If you comment out this assert, \Moko\Integrated\InvocationExpectationFailureException will be thrown
         */
        $this->assertEquals('done', $obj->method1(null, array(), new \stdClass()));
    }
}
