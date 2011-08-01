<?php

namespace Moko;

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

class _AnotherMockClass
{
    public $foo;

    public $bar;


    public function blah()
    {
        
    }

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class _ConstructorAndOtherMethodInvocationFromIt
{
    protected function doSomethingMethod()
    {

    }

    public function __construct()
    {
        $this->doSomethingMethod();
    }


}

class _MockDelegateClass
{
    public $foo = 'foo';

    public function doFoo()
    {
        $this->foo = 'foo-foo';
    }

    public function doBar()
    {

    }
}

class _MockWithFinalMethod
{
    final function doFoo()
    {

    }

    public function doBar()
    {
        
    }
}

class _MockWithReturningMethod
{
    public function getSomething($bar)
    {
        return 'something-'.$bar;
    }
}