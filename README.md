Afrikapié Web Book
==================

Source code of the Afrikapié web book.

Installation
------------

Firstly, clone the **Afrikapié Web Book** repository:
``git clone https://github.com/freepius/afrikapie-web-book.git``

Secondly, install [Composer] (the most popular dependency manager for PHP):
``php -r "readfile('https://getcomposer.org/installer');" | php``

Finally, install the project dependencies: ``php composer.phar install``

Tests
-----

To run the test suite, you need :

1. to install the *development* dependencies: ``php composer.phar install --dev``
1. to execute [PHPUnit]: ``vendor/bin/phpunit`` or ``vendor/bin/phpunit tests/My/Particular/Test.php``

License
-------

The **Afrikapié Web Book** is licensed under the **CC0** license.

TODO
----

* AfrikapieText: rename replaceCollection() by applyCollection()
* BUG of "replace the replacements"


[Composer]: http://getcomposer.org
[PHPUnit]: https://phpunit.de
