# Moko

Moko is a lighweight mocking mini-framework that sits on top of PHP5.3+, the main goal
that this framework is intended to solve it to make mocking objects in PHP really easy. It
is very easy to get started using its because its API consists only of two methods, namely:
 * __constructor($targetName, $omitConstructor)
 * addMethod($methodName, \Closure $callback)

A typical usage workflow could look like this:

Say, you have an interface "MyInterface":

```php
interface MyInterface
{
    function execute($input);
}
```

And you need to create a mock for this class, with Moko it would look like this:

```php
// if second parameters is provided then default constructor will be overridden with a non-params one
$ma = new \Moko\MockAsssembler('MyInterface');

// first parameter of the provided closure will be an instance of the mock object or
// if a method is static then it is going to be mock's FQCN
$ma->addMethod('execute', function($self, $input) {
    echo "Executed with $input";
});

// here you are able to pass parameters to constructor
$obj = $ma->createMock();

// will return "Executed with Foo-bar-baz"
$obj->execute('Foo-bar-baz');
```

Take a look at the source code of \Moko\MockAsssembler for more details.