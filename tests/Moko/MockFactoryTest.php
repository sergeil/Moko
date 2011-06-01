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

require_once '../../src/Moko/ClassLoader.php';
ClassLoader::register();

use Moko\MockFactory;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.org>
 */ 
class MockFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        $mf1 = new MockFactory($this);
        $mf2 = new MockFactory();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test__constructor_invalidArgument()
    {
        $mf = new MockFactory('scalar');
    }

    public function testCreate()
    {
        $mf = new MockFactory($this);

        $md = $mf->create('stdClass');
        $this->assertTrue($md instanceof \Moko\MockDefinition);
    }

    public function testCreateTestCaseAware()
    {
        $mf = new MockFactory($this);

        $md = $mf->createTestCaseAware('stdClass');
        $this->assertTrue($md instanceof \Moko\TestCaseAwareMockDefinition);
        $this->assertSame($this, $md->getTestCase());
    }
}
