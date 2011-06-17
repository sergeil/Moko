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
    public function __construct($a, $b)
    {

    }
}