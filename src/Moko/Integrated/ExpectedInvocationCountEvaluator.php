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
 * You may implement this interface and inject it to {@class TestCaseAwareMockDefinition} if
 * you need more fancy logic for evaluating expectations count validity.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
interface ExpectedInvocationCountEvaluator
{
    /**
     * @throws InvocationExpectationFailureException  If expectations are not satisfied
     * @param string $targetName  Name of original interface/class a mock is created for
     * @param mixed $mock  The mock object itself
     * @param string $methodName  Method name we are evaluating expectations at the moment for
     * @param string $aliasName  Alias name that was assigned to this mock object
     * @param array $invocationCounters  All expectations that were tracked for this mock object
     * @param integer $expectedInvocationsCount  An instruction that must be satisfied not to have an exception thrown
     */
    public function evaluate($targetName, $mock, $methodName, $aliasName, array $invocationCounters, $expectedInvocationsCount);
}
