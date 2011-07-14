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

/**
 * This exception should be thrown when expectations about how many times
 * a method should have been invoked do not met.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class InvocationExpectationFailureException extends \RuntimeException
{
    protected $expected;

    protected $actual;
    
    protected $aliasName;

    public function __construct($class, $method, $expected, $actual, $aliasName = null)
    {
        $this->expected = $expected;
        $this->actual = $actual;
        $this->aliasName = $aliasName;

        $this->message = sprintf(
            'Method %s::%s was expected to be invoked %s times but instead was %s times.',
            $class, $method, $expected, $actual
        );

        if (null !== $aliasName) {
            $this->message .= "( mock-alias: '$aliasName')";
        }
    }

    public function getActual()
    {
        return $this->actual;
    }

    public function getExpected()
    {
        return $this->expected;
    }

    public function getAliasName()
    {
        return $this->aliasName;
    }
}
