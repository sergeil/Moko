# Moko

Moko is a lightweight mocking mini-framework that sits on top of PHP5.3+, the main problem
Moko is intended to solve is to allow you mocking classes in an efficient way. The main thing
that differentiates Moko from other existing solutions is that you do not need to lear another DSL, because Moko uses, as someone may say,
"dirty" mocking approach which leverages closures(callbacks).

Let's take a look at a use case in which Moko is able to help you in a very efficient way. Say,
you have an interface(Provider) with some method(get), your intention is to test another dependent class,
but the problem is that the aforementioned method will be invoked multiple times with different parameters and
the execution result must change according to the input parameters.

```php

interface Provider
{
    public function get($id);
}

/**
 * Will emulate a "scoped" singleton.
 */
class Context
{
    /**
     * @var Provider
     */
    private $provider;

    private $context = array();

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function get($id)
    {
        if (!isset($this->context[$id])) {
            $this->context[$id] = $this->provider->get($id);
        }

        return $this->context[$id];
    }
}

```

Here is how the test would look like:

```php
class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Moko\MockFactory
     */
    private $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function testGet()
    {
        $providerDef = $this->mf->createTestCaseAware('Provider');

        // $self here will be an instance of mocked object or FQCN if method is static
        $providerDef->addMethod('get', function($self, $id) {
            if ($id == 'foo1') {
                return new \DomainObject();
            } else {
                return new \AnotherDomainObject();
            }
        }, 2); // last argument specifies how many times the method is expected to be invoked

        /*
         * createMock method accepts two parameters:
         * 1 - array of parameters to you want to pass to the constructor
         * 2 - feed "true" if you want to have a non-parameters constructor to be generated for you
         */
        $provider = $providerDef->createMock();

        $context = new Context($provider);
        $d1 = $context->get('foo1');
        $this->assertSame($d1, $context->get('foo1'));

        $d2 = $context->get('fooX');
        $this->assertSame($d2, $context->get('fooX'));
    }
}
```

In general Moko is made of three classes with very simple API:

 - MockDefinition: This class heart of the Moko, it provides the backbone functionality for mocking
 - TestCaseAwareMockDefinition: This class complements MockDefinition class and provides easy way of method invocation count validation
 - MockFactory: An auxiliary class that may be used to create instances of two aforementioned classes, will be useful if you need
   to deal with several TestCaseAwareMockDefinition in a single TestCase.

# What's next ?
 - Play with, any feedback is highly appreciated
 - Take a look at /sandbox/PhpUnitIntegration.php
 - Read the source code