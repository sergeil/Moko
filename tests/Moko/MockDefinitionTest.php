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

require_once __DIR__.'/../bootstrap.php';

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class MockDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        $ma1 = new MockDefinition('Moko\_MockInterface');
        $this->assertEquals(
            'Moko\_MockInterface',
            $ma1->getTargetName(),
            'Target name passed to constructor and value returned by "getTargetName" diverged.'
        );
        $this->assertTrue($ma1->isConstructorOmitted(), 'By default constructor should be omitted.');

        $ma2 = new MockDefinition('Moko\_MockClass', false);
        $this->assertEquals('Moko\_MockClass', $ma2->getTargetName());
        $this->assertFalse($ma2->isConstructorOmitted());
    }

    /**
     * If constructor is intentionally omitted it shouldn't be mocked automatically
     */
    public function test__createMockWithOmittedConstructor()
    {
        $ma1 = new MockDefinition('Moko\_AnotherMockClass');
        $ma1->createMock();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test__construct_unexistingClass()
    {
        $ma1 = new MockDefinition('FooClazz');
    }

    public function testCreateMock_interface()
    {
        $ma = new MockDefinition('Moko\_MockInterface', null, false);
        $ma->addMethod('doFoo', function($cx, $param1) {
            $cx->param1Value = $param1; // dynamically creating a new variable
        });

        $staticCheck = new \stdClass();

        $chainedMa = $ma->addMethod('doBlah', function($fqcn) use ($staticCheck) {
            $staticCheck->fqcn = $fqcn;
        });

        $this->assertSame($ma, $chainedMa);

        $instance = $ma->createMock(array(), 'FooAlias');
        $this->assertTrue($instance instanceof _MockInterface);

        $instance->doFoo('foobaz');
        $this->assertEquals('foobaz', $instance->param1Value, "Defined callback method wasn't invoked");

        $instance::doBlah();
        $this->assertEquals(get_class($instance), $staticCheck->fqcn);

        $isThrown = false;
        try {
            $instance->doBar();
        } catch (UnexpectedInteractionException $e) {
            $isThrown = true;
            $this->assertEquals('FooAlias', $e->getAliasName());
        }
        $this->assertTrue($isThrown);
        
        $reflClass = new \ReflectionClass($instance);
        $reflMethod = $reflClass->getMethod('doFooBar');
        $reflParams = $reflMethod->getParameters();

        $this->assertEquals('param1', $reflParams[0]->getName());
        $this->assertEquals('param2', $reflParams[1]->getName());
        $this->assertEquals('param3', $reflParams[2]->getName());
        $this->assertEquals('param4', $reflParams[3]->getName());

        $this->assertEquals(array('foo', 'bar'), $reflParams[2]->getDefaultValue());
        $this->assertEquals(null, $reflParams[3]->getDefaultValue());
        
        $reflDoBar = $reflClass->getMethod('doBar');
        $this->assertTrue(strpos($reflDoBar->getDocComment(), 'bardoc') !== false, "Method docblock must be inherited from target interface/class.");
    }

    public function testCreateMock_class()
    {
        $ma1 = new MockDefinition('Moko\_MockClass');
        $instance = $ma1->createMock();
        $this->assertTrue($instance instanceof _MockClass);
    }

    public function testCreateMock_withDelegateMethod()
    {
        $ma = new MockDefinition('Moko\_MockDelegateClass', null, false);
        $maChain = $ma->addDelegateMethod('doFoo');

        $this->assertSame($ma, $maChain);

        /* @var \Moko\_MockDelegateClass $mock */
        $mock = $ma->createMock();

        $this->assertTrue($mock instanceof \Moko\_MockDelegateClass);

        $this->assertEquals('foo', $mock->foo);
        $mock->doFoo();
        $this->assertEquals(
            'foo-foo',
            $mock->foo,
            sprintf("Parent method Moko\_MockDelegateClass::doFoo was not invoked, in other words - parent method wasn't invoked.")
        );

        $isThrown = false;
        try {
            $mock->doBar();
        } catch (UnexpectedInteractionException $e) {
            $isThrown = true;
        }
        $this->assertTrue(
            $isThrown,
            '\Moko\UnexpectedInteractionException exception must have been thrown for a method with no manually callback defined'
        );
    }

    public function testCreateMock_forClassWithFinalMethods()
    {
        $ma = new MockDefinition('Moko\_MockWithFinalMethod');
        $obj = $ma->createMock();

        $this->assertType('Moko\_MockWithFinalMethod', $obj);
    }

    public function testCreateMock_withShorthandReturnValueCallbackDefinition()
    {
        $ma1 = new MockDefinition('Moko\_MockWithFinalMethod');
        $ma1->addMethod('doBar', "foo-bar");

        /* @var \Moko\_MockWithFinalMethod @obj */
        $obj = $ma1->createMock();
        $this->assertType('Moko\_MockWithFinalMethod', $obj);
        $this->assertEquals(
            'foo-bar',
            $obj->doBar(),
            'If second parameter of the addMethod() method is not a callback then it should be returned when a mocked method is invoked'
        );


        $returnValue = new \stdClass();
        $ma2 = new MockDefinition('Moko\_MockWithFinalMethod');
        $ma2->addMethod('doBar', $returnValue);

        $obj = $ma2->createMock();
        $this->assertType('Moko\_MockWithFinalMethod', $obj);
        $this->assertSame(
            $returnValue,
            $obj->doBar(),
            sprintf(
                'It was assumed that %s::doBar() would return the same instance of object that was passed as second argument to %s::addMethod() while defining its mock method.',
                'Moko\_MockWithFinalMethod', 'Moko\MockDefinition'
            )
        );

        
        $ma3 = new MockDefinition('Moko\_MockWithFinalMethod');
        $ma3->addMethod('doBar');

        $obj = $ma3->createMock();
        $this->assertType('Moko\_MockWithFinalMethod', $obj);
        $this->assertNull($obj->doBar());
    }
}
