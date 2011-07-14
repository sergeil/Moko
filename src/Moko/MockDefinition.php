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
     * If targetName interface/class hasn't been loaded yet we will try to load it behind the scene,
     * it is up to you to register your class-loaders beforehand.
     *
     * @throws \InvalidArgumentException  If a $targetName class/interface doesn't exist
     * @param string $targetName  Name of an interface/class name you want to mock
     * @param boolean $omitConstructor If the parent's constructor should be overriden with a non-parameters one
     */
    public function __construct($targetName, $omitConstructor = true)
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
     * @param  string $methodName
     * @param \Closure|string|null $callbackOrReturnValue  A closure that will be invoked when an original method is invoked.
     *                                                     An instance of mock object will be passed as a first parameter of the closure,
     *                                                     if a method is static then mock's FQCN will be passed instead. Also it is possible
     *                                                     to pass a literal and it will be returned when the method is invoked.
     * @return \Moko\MockDefinition
     */
    public function addMethod($methodName, $callbackOrReturnValue = null)
    {
        $this->checkMethodExistence($methodName);

        $this->definitions[$methodName] = array(
            'isDelegate' => false,
            'isDynamic' => false,
            'callback' => $callbackOrReturnValue
        );

        return $this;
    }

    /**
     * Allows to invoke a parent method of mocked class, the stress here is - class, you are
     * not able to invoke methods from interface because they have no bodies.
     *
     * @throws \InvalidArgumentException  If mocking target is interface or method is marked as abstract
     * @param  $methodName
     * @return \Moko\MockDefinition
     */
    public function addDelegateMethod($methodName)
    {
        $rt = $this->getReflectedTarget();
        if ($rt->isInterface()) {
            $msg = 'Method %s::%s cannot be mocked because target (%s) you are mocking out is an interface, ';
            $msg .= 'delegate methods can be created only for non-abstract methods.';
            $msg = sprintf(
                $msg,
                $this->getTargetName(), $methodName, $this->getTargetName()
            );
            throw new \InvalidArgumentException($msg);
        }
        $this->checkMethodExistence($methodName);
        if ($rt->getMethod($methodName)->isAbstract()) {
            $msg = sprintf(
                "Unable to create a delegate method for method %s::%s because it is abstract!",
                $this->getTargetName(), $methodName
            );
            throw new \InvalidArgumentException($msg);
        }

        $this->definitions[$methodName] = array(
            'isDelegate' => true
        );

        return $this;
    }

    /**
     * @throws \InvalidArgumentException  If the provided method is not declared in target's class/interface
     * @param  $methodName
     * @return void
     */
    protected function checkMethodExistence($methodName)
    {
        if (!$this->getReflectedTarget()->hasMethod($methodName)) {
            $targetType = $this->getReflectedTarget()->isInterface() ? 'interface' : 'class';
            $msg = sprintf(
                "Method %s::%s you are attempting to mock is not declared in %s %s.",
                $this->getTargetName(), $methodName, $targetType,
                $this->getTargetName(), $this->getTargetName()
            );
            throw new \InvalidArgumentException($msg);
        }
    }

    protected function createTemplateData($constructorParams, $suppressUnexpectedInteractionExceptions)
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

        $reflTarget = $this->getReflectedTarget();
        foreach ($reflTarget->getMethods() as $reflMethod) {
            // omitted constructor will be properly handled in the template itself
            // and there's no way to override final methods - skipping them too
            if (($data['omitConstructor'] && $reflMethod->getName() == '__construct') ||
                $reflMethod->isFinal()) {
                continue;
            }

            $methodName = $reflMethod->getName();
            $data['methods'][$methodName] = $this->createMethodConfigurationArray($reflMethod);
        }

        $data['namespace'] = $reflTarget->getNamespaceName();
        $data['targetRelationship'] = $reflTarget->isInterface() ?  'implements' : 'extends';
        $data['targetDocBlock'] = $reflTarget->getDocComment();

        return $data;
    }

    private function createMethodConfigurationArray(\ReflectionMethod $reflMethod)
    {
        $methodName = $reflMethod->getName();

        // these ones will be used to create a proper callback invocation
        $paramNames = array();
        foreach ($reflMethod->getParameters() as $reflParam) {
            $paramNames[] = '$'.$reflParam->getName();
        }

        // having abstract methods is not allowed
        $modifiers = array_flip(\Reflection::getModifierNames($reflMethod->getModifiers()));
        unset($modifiers['abstract']);
        $modifiers = array_flip($modifiers);

        $isDelegate = isset($this->definitions[$reflMethod->getName()]) && $this->definitions[$reflMethod->getName()]['isDelegate'] === true;

        return array(
            'isDelegate' => $isDelegate,
            'isExplicetelyDefined' => isset($this->definitions[$reflMethod->getName()]),
            'docBlock' => $reflMethod->getDocComment(),
            'modifiers' => $modifiers,
            'params' => $this->createMethodParams($reflMethod),
            'paramNames' => $paramNames,
            'isStatic' => $reflMethod->isStatic(),
            'callback' => isset($this->definitions[$methodName]) && isset($this->definitions[$methodName]['callback']) ? $this->definitions[$methodName]['callback'] : null
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

    /**
     * @param array $constructorParams  Parameters that you want to pass to the constructor
     * @param string $aliasName  You may this an alias to simplify distinguishing produced mock-objects
     * @param boolean $suppressUnexpectedInteractionExceptions  By default if you haven't explicetely mocked a method
     *                                                          then {@class Moko\UnexpectedInteractionException} will be thrown, use
     *                                                          this parameters to change this behaviour.
     * @return object  An instance of provided in constructor $targetName
     */
    public function createMock(array $constructorParams = array(), $aliasName = null, $suppressUnexpectedInteractionExceptions = false)
    {
        $data = $this->createTemplateData($constructorParams, $suppressUnexpectedInteractionExceptions);


        $compiledSource = $this->compileTemplate($data);
        eval($compiledSource);
        
        $mockClassName = $data['className'];
        $reflMockClass = new \ReflectionClass($data['namespace'].'\\'.$mockClassName);


        $callbacks = array();
        foreach ($this->definitions as $methodName=>$methodDef) {
            if (!$this->definitions[$methodName]['isDelegate']) { // no callback should exist for delegate methods
                $callbacks[$methodName] = $methodDef['callback'];
            }
        }
        $reflMock = new \ReflectionClass($this->getReflectedTarget()->getNamespaceName().'\\'.$mockClassName);
        $reflMock->getProperty('____callbacks')->setValue(null, $callbacks);
        $reflMock->getProperty('____aliasName')->setValue(null, $aliasName);

        /*
         * Achtung!
         * MockDefinitionTest::testCreateMock_omitConstructorAndUseDelegateOne:
         * 
         * Instance should be create only when all required static properties are injected, this
         * is important when you have a mocked method that is invoked from delegated constructor.
         */
        $obj = $this->getReflectedTarget()->hasMethod('__construct') ? $reflMockClass->newInstanceArgs($constructorParams) : $reflMockClass->newInstance();

        return $obj;
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