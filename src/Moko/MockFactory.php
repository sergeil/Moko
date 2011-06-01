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

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class MockFactory 
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    protected $testCase;

    /**
     * @throws \InvalidArgumentException
     * @param \PHPUnit_Framework_TestCase $testCase  This test case will be passed to {@class Moko\TestCaseAwareMockDefinition} and
     *                                               if you are not going to use {#createTestCaseAware} you may simply pass NULL value.
     */
    public function __construct($testCase = null)
    {
        if ($testCase !== null && !($testCase instanceof \PHPUnit_Framework_TestCase)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument of %s::%S must be either null value or an instance of the \PHPUnit_Framework_TestCase',
                    __CLASS__, __METHOD__
                )
            );
        }

        $this->testCase = $testCase;
    }

    /**
     * @see MockAssembler::__construct()
     * @return MockDefinition
     */
    public function create($targetName, $omitConstructor = false)
    {
        return new MockDefinition($targetName, $omitConstructor);
    }

    /**
     * @see TestCaseAwareMockDefinition::__construct()
     * @return TestCaseAwareMockDefinition
     */
    public function createTestCaseAware($targetName, $omitConstructor = false)
    {
        return new TestCaseAwareMockDefinition($this->testCase, $targetName, $omitConstructor);
    }
}
