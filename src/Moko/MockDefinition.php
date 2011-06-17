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
class MockDefinition
{
    /**
     * @var string
     */
    protected $targetName;

    /**
     * @var array
     */
    protected $definitions = array();

    /**
     * @var boolean
     */
    protected $isConstructorOmitted;

    /**
     * @var \ReflectionClass
     */
    protected $reflTarget;

    /**
     * @return \ReflectionClass
     */
    protected function getReflectedTarget()
    {
        if (null === $this->reflTarget) {
            $this->reflTarget = new \ReflectionClass($this->getTargetName());
        }

        return $this->reflTarget;
    }

    /**
     * @return string
     */
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * @return boolean
     */
    public function isConstructorOmitted()
    {
        return $this->isConstructorOmitted;
    }

    /**
     * If targetName interface/class hasn't been loaded yet we will try to load it behind the scene.
     *
     * @throws \InvalidArgumentException  If a $targetName class/interface doesn't exist
     * @param string $targetName  Name of an interface/class name you want to mock
     * @param boolean $omitConstructor Wether the parent's constructor should be overriden with a non-parameters one
     */
    public function __construct($targetName, $omitConstructor = false)
    {
        if (!class_exists($targetName) && !interface_exists($targetName)) {
            throw new \InvalidArgumentException(
                "Class/interface '$targetName' you are trying to create a mock for doesn't exist."
            );
        }

        $this->targetName = $targetName;
        $this->isConstructorOmitted = $omitConstructor;
    }

    /**
     * @throws \InvalidArgumentException  If the provided method is not declared in target's class/interface
     * @param  string $methodName
     * @param \Closure $callback  A closure that will be invoked when an original method is invoked.
     *                            An instance of mock object will be passed as a first parameter of the closure,
     *                            if a method is static then mock's FQCN will be passed instead.
     * @param bool $isStatic
     * @return \Moko\MockDefinition
     */
    public function addMethod($methodName, \Closure $callback)
    {
        if (!$this->getReflectedTarget()->hasMethod($methodName)) {
            $targetType = $this->getReflectedTarget()->isInterface() ? 'interface' : 'class';
            $msg = sprintf(
                "Method %s::%s you are attempting to mock is not declared in %s %s. If you need to mock a method which is not declared in %s, then use 'addDynamicMethod' instead!",
                $this->getTargetName(), $methodName, $targetType,
                $this->getTargetName(), $this->getTargetName()
            );
            throw new \InvalidArgumentException($msg);
        }

        $this->definitions[$methodName] = array(
            'isDynamic' => false,
            'callback' => $callback
        );

        return $this;
    }

    /**
     * @param array $constructorParams  Parameters that you want to pass to the constructor
     * @param boolean $suppressUnexpectedInteractionExceptions  By default if you haven't explicetely mocked a method
     *                                                          then {@class Moko\UnexpectedInteractionException} will be thrown, use
     *                                                          this parameters to change this behaviour.
     * @return object  An instance of provided in constructor $targetName
     */
    public function createMock(array $constructorParams = array(), $suppressUnexpectedInteractionExceptions = false)
    {
        // this one will be used while compiling the template
        $data = array(
            'suppressUnexpectedInteractionExceptions' => $suppressUnexpectedInteractionExceptions,
            'className' => $this->composeMockClassName(),
            'omitConstructor' => $this->isConstructorOmitted(),
            'methods' => array(),
            'namespace' => null,
            'targetRelationship' => null,
            'targetDocBlock' => '',
            'targetName' => $this->getTargetName(),
            'nonMockedMethodNames' => array()
        );

        $nonMockedMethodNames = array_diff(
            get_class_methods($this->getTargetName()),
            array_keys($this->definitions)
        );
        
        $reflTarget = $this->getReflectedTarget();
        foreach ($reflTarget->getMethods() as $reflMethod) {
            if ($data['omitConstructor'] && $reflMethod->getName() == '__construct') {
                continue;
            }

            $methodName = $reflMethod->getName();
            $data['methods'][$methodName] = $this->createMethodConfigurationArray($reflMethod);
        }

        $data['namespace'] = $reflTarget->getNamespaceName();
        $data['targetRelationship'] = $reflTarget->isInterface() ?  'implements' : 'extends';
        $data['targetDocBlock'] = $reflTarget->getDocComment();

        $compiledSource = $this->compileTemplate($data);
        eval($compiledSource);
        
        $mockClassName = $data['className'];
        $reflMockClass = new \ReflectionClass($data['namespace'].'\\'.$mockClassName);

        $obj = $reflTarget->hasMethod('__construct') ? $reflMockClass->newInstanceArgs($constructorParams) : $reflMockClass->newInstance();

        $callbacks = array();
        foreach ($this->definitions as $methodName=>$methodDef) {
            $callbacks[$methodName] = $methodDef['callback'];
        }
        $reflMock = new \ReflectionClass($reflTarget->getNamespaceName().'\\'.$mockClassName);
        $reflMock->getProperty('____callbacks')->setValue(null, $callbacks);

        return $obj;
    }

    private function createMethodConfigurationArray(\ReflectionMethod $reflMethod)
    {
        $paramNames = array();
        foreach ($reflMethod->getParameters() as $reflParam) {
            $paramNames[] = '$'.$reflParam->getName();
        }

        $modifiers = array_flip(\Reflection::getModifierNames($reflMethod->getModifiers()));
        unset($modifiers['abstract']);
        $modifiers = array_flip($modifiers);

        return array(
            'isExplicetelyDefined' => isset($this->definitions[$reflMethod->getName()]),
            'docBlock' => $reflMethod->getDocComment(),
            'modifiers' => $modifiers,
            'params' => $this->createMethodParams($reflMethod),
            'paramNames' => $paramNames,
            'isStatic' => $reflMethod->isStatic()
        );
    }

    private function createMethodParams(\ReflectionMethod $reflMethod)
    {
        $paramsArray = array();

        foreach ($reflMethod->getParameters() as $methodParam) {
            $signature = '';

            if (is_object($methodParam->getClass())) {
                $signature .= '\\'.$methodParam->getClass()->getName().' ';
            } else if ($methodParam->isArray()) {
                $signature .= 'array ';
            }

            $signature = $signature.'$'.$methodParam->getName();

            if ($methodParam->isDefaultValueAvailable()) {
                $computedDefaultValue = var_export($methodParam->getDefaultValue(), true);

                $signature .= ' = '.$computedDefaultValue;
            }

            $paramsArray[] = $signature;
        }
        
        return $paramsArray;
    }

    protected function getTemplateFilename()
    {
        return __DIR__.'/tpls/class.tpl';
    }

    protected function compileTemplate(array $importVars)
    {
        $templateSource = file_get_contents($this->getTemplateFilename());
        $templateSource = '?>' . $templateSource . '<?php'. "\n";

        // importing variables into local scope so they are accessible from within the template
        foreach ($importVars as $name=>$value) {
            $$name = $value;
        }

        ob_start();
        eval($templateSource);
        $result = ob_get_clean();

        return str_replace(array('[?', '?]'), array('<?php', '?>'), $result);
    }
    
    protected function composeMockClassName()
    {
        $cn = str_replace(array('\\'), '_', $this->targetName);
        $cn .= '_'.str_replace('.', '', microtime(true));
        $cn .= '_'.rand(1, 1000);
        $cn .= '_'.spl_object_hash($this);

        return $cn; 
    }
}