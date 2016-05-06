# Container

A set of container classes that implement the forthcoming [Psr-11 container interface](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md) (by extension, the [current container interop interface](https://github.com/container-interop/container-interop)). Some of these classes are very simple (the ServerContainer simply uses $_SERVER to store/retrieve data with no other changes from the base container class), but some are significantly more complicated (InjectorFactoryContainer does *magic!*).  Here's a diagram of the 7 classes and a really brief description of how they relate/differ:

![diagram of classes](https://raw.githubusercontent.com/dev-lucid/container/master/docs/class_diagram.png)


## Reasons for developing/using these classes:

I originally wrote these classes to provide a common API for accesing session variables and request variables that could be configured to point to mock data for unit testing. Eventually it turned into a more general purpose collection of classes that could form the basis for a framework using either a [service locator pattern](https://en.wikipedia.org/wiki/Service_locator_pattern) or [dependency injection pattern](https://en.wikipedia.org/wiki/Dependency_injection); or a mix of the two.

# Basic Stuff

## Setting/getting data out of a container
There are 2 functions for setting data in your container:

* ->set($id, $newValue), pretty self explanatory.
* ->setValues(array $newValues). This iterates over an array using the array's indexes are keys. 

There are 8 functions for getting data out of a container:

* ->get($id), which performs no type casting at all. This is based on Psr-11's [ContainerInterface](https://github.com/container-interop/container-interop/blob/master/docs/ContainerInterface.md)
* ->getValues(), which returns all of the containers keys / values as an associated array. This may be useful for debugging, but if you have a bunch of objects in your container, calling print_r may dump a LOT of data. 
* ->string(string $id, string $defaultValue), which calls strval on the data first. $defaultValue is returned if the index is not set (unlike ->get, which will throw an error)
* ->int($id, int $defaultValue), which calls intval on the data first. $defaultValue is returned if the index is not set (unlike ->get, which will throw an error)
* ->float($id float $defaultValue), which calls floatval on the data first. $defaultValue is returned if the index is not set (unlike ->get, which will throw an error)
* ->bool($id, bool $defaultValue), which calls boolval on the data first. $defaultValue is returned if the index is not set (unlike ->get, which will throw an error)
* ->DateTime($id, DateTime $defaultValue), which attempts to convert the data into a DateTime object. $defaultValue is returned if the index is not set (unlike ->get, which will throw an error)
* ->array($id, array $defaultValue, string $delimiter=','), which converts the data into an array, and returns the array. If the index did not contain an array, the value will be exploded using a delimiter, which is a comma by default. 

Here's a couple simple examples:

```php
$container = new \Lucid\Container\Container();

$container->set('mystring', 'my value');
echo($container->get('mystring'));    # echos with no conversion at all
echo($container->string('mystring')); # calls strval() on the data first

$container->set('myint', 1);
echo($container->string('myint')); # calls strval() on the data first, you get back '1'
echo($container->int('myint')); # calls intval on your data first, so you should get back 1

$container->set('mydate', '2010-01-01T12:00:00+00:00');
print_r($container->DateTime('mydate'));
# In this case, the function will return a DateTime object. print_r
# should print out something like this:
#
# DateTime Object
#(
#    [date] => 2010-01-01 12:00:00.000000
#    [timezone_type] => 1
#    [timezone] => +00:00
#)

```

### DateTime formats

When trying to convert a value to a DateTime object, by default Container will call \DateTime::createFromFormat and try 3 different formats in order: \DateTime::ISO8601, \DateTime::W3C, 'U'. If any of those attempts does not fail, the result is returned. You can set which formats are tried by calling ->setDateTimeFormats(...$newFormats). Note that this replaces which formats are tested, it is NOT additive.

## Using __call()

Both Container and PrefixDecorator allow you to use __call to access indexes, but only for getting, not setting. For example:

```php
$app = new \Lucid\Container\Container();
$app->set('myindex', 'myvalue');
echo($app->get('myindex'));
 # echos 'myvalue', as expected
echo($app->myindex());
 # Also echos 'myvalue'
```


### Booleans, how RequestContainer differs

### CookieContainer settings

## Locking data
You can lock indexes to prevent accidental overwriting by using the ->lock($id) and ->unlock($id) methods. For example:

```php
$config = new \Lucid\Container\Container();
$config->set('admin-username', 'admin');
$config->lock('admin-username');
$config->set('admin-username', 'user1234'); # Throws a glorious exception!
$config->unlock('admin-username');
$config->set('admin-username', 'user1234'); # Will ungloriously succeed
```
Note: basically nothing prevents an index from being unlocked, so if you're really worried about malicious activity, locking the index is not going to stop some other code from changing values. This is really just there to prevent you from accidentally overwriting something.

## Exception Classes

A number of catchable exception classes are used:

* Lucid\Container\Exception\InvalidSourceException: thrown when ->setSource is called, but the new source is not usable. A usable source may be either an array, or an object that implements both the [ArrayAccess interface](http://php.net/manual/en/class.arrayaccess.php) and the [Iterator interface](http://php.net/manual/en/class.iterator.php)
* Lucid\Container\Exception\DateTimeParseException: thrown when calling ->DateTime(), but the value could not be converted into a DateTime object using any of the registered formats. 
* Lucid\Container\Exception\InvalidBooleanException: thrown when calling ->bool(), but the value could not be converted into a bool using any of the acceptable true/false values.
* Lucid\Container\Exception\LockedIndexException: Thrown when ->set() is called and the index that you're trying to set has been locked by ->lock(). 
* Lucid\Container\Exception\NotFoundException: thrown when ->get() is called, but the index does not exist in the container. This exception is required by [Psr-11](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md). Notably none of the typed getters will throw an exception when accessing an index that does not exist, as they all have a $defaultValue parameter that will be returned instead. 
* Lucid\Container\RequiredInterfaceException: thrown when setting an index that has been configured to only accept objects that implement one or more specific interfaces. Notably this will also be thrown if the index was set, and then the interface requirement is configured.


# Advanced Stuff
* [Neat ideas on using as a service locator](ServiceLocator.md)
* [Closures and how to store/call them](Closures.md)
* [More info on the PrefixDecorator class](PrefixDecorator.md)
* [All about how to use the InjectorFactoryContainer class](InjectorFactory.md)
* [Notes on the delegate container functionality](DelegateContainer.md)