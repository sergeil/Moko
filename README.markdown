# Moko

Moko is a lightweight mocking mini-framework that sits on top of PHP5.3+, the main problem
Moko is intended to solve is to allow you mocking classes in an efficient way. The main thing
that differentiates Moko from other existing solutions is that you do not need to learn another DSL, because Moko uses, as someone may say, "dirty" mocking approach which leverages closures(callbacks). Moko also has integration also provide some integration with PHPUnit.

### Teaser

Say, you have an interface, in our case it is going to look like this one:

```php
interface UserDao
{
    public function findOneByPk($pk);
}
```

And you need to have a mock that would return different instances of User object accordingly to the provided primary key, with Moko all you need to do is ( we pretend that this snippet is located in PHPUnit's TC method ):

```php
$testUsers = array(
    1 => new User('John Doe'),
    2 => new User('Jane Doe')
);

$moko = new \Moko\MockDefinition('UserDao');
$moko->addMethod('findOneByPk', function($service, $pk) use ($testUsers) {
    return isset($testUsers[$pk]) ? $testUsers[$pk] : null;
});
$serviceMock = $moko->createMock();

$this->assertType('UserDao', $serviceMock);
$this->assertSame($serviceMock->findOneByPk(1), $testUsers[1]);
$this->assertSame($serviceMock->findOneByPk(2), $testUsers[2]);
```

Moko provides intuitive integration mechanism with PHPUnit, for more examples and more elaborate examples please 
use wiki - https://github.com/sergeil/Moko/wiki.