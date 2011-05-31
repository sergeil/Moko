<?php

namespace Moko;

require_once implode(DIRECTORY_SEPARATOR, array('..', '..', 'src', 'Moko', 'MockAssembler.php'));
require_once implode(DIRECTORY_SEPARATOR, array('..', '..', 'src', 'Moko', 'UnexpectedInteractionException.php'));
require_once implode(DIRECTORY_SEPARATOR, array('PHPUnit', 'Framework', 'TestCase.php'));

interface _MockInterface
{
    public function doFoo($param1);

    /**
     * bardoc
     */
    public function doBar();

    public function doFooBar(\stdClass $param1, array $param2, array $param3 = array('foo', 'bar'), $param4 = null);

    static public function doBlah();
}

class _MockClass
{

}

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class MockAssemblerTest extends \PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        $ma1 = new MockAssembler('Moko\_MockInterface', true);
        $this->assertEquals('Moko\_MockInterface', $ma1->getTargetName());
        $this->assertTrue($ma1->isConstructorOmitted());

        $ma2 = new MockAssembler('Moko\_MockClass', false);
        $this->assertEquals('Moko\_MockClass', $ma2->getTargetName());
        $this->assertFalse($ma2->isConstructorOmitted());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test__construct_unexistingClass()
    {
        $ma1 = new MockAssembler('FooClazz');
    }

    public function testCreateMock_interface()
    {
        $ma = new MockAssembler('Moko\_MockInterface');
        $ma->addMethod('doFoo', function($cx, $param1) {
            $cx->param1Value = $param1; // dynamically creating a new variable
        });

        $staticCheck = new \stdClass();

        $ma->addMethod('doBlah', function($fqcn) use ($staticCheck) {
            $staticCheck->fqcn = $fqcn;
        });

        $instance = $ma->createMock();
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
        $ma1 = new MockAssembler('Moko\_MockClass', true);
        $instance = $ma1->createMock();
        $this->assertTrue($instance instanceof _MockClass);
    }
}
