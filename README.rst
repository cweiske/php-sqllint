***********
PHP-SQLlint
***********

Command line tool to validate (syntax check) SQL files.
Primarily for MySQL ``.sql`` files.

Can be used in git pre-commit hooks to catch errors.
Use it from your shell, offline and without any SQL server.

You can also use it to format SQL queries.


=====
Usage
=====
Syntax check::

    $ php-sqllint tests/files/create-missingcomma.sql 
    Checking SQL syntax of tests/files/create-missingcomma.sql
     Line 3, col 5 at "pid": A comma or a closing bracket was expected.
     Line 3, col 13 at "11": Unexpected beginning of statement.
     Line 3, col 17 at "DEFAULT": Unrecognized statement type.

Emacs mode::

    $ php-sqllint -r emacs tests/files/create-noname.sql 
    tests/files/create-noname.sql:1.12:Error: The name of the entity was expected.
    tests/files/create-noname.sql:1.13:Error: A closing bracket was expected.
    tests/files/create-noname.sql:1.13:Error: At least one column definition was expected.


Formatting::

    $ php-sqllint --format tests/files/select-unformatted.sql
    SELECT
      id,
      NAME,
      url
    FROM
      users
    WHERE
      DATE > NOW() AND id != 0
    ORDER BY NAME
    LIMIT 10


Syntax highlighting
===================
ANSI colors are applied automatically when not piping; you can use the
``--highlight`` option to override the automatism.

``--highlight`` option values:

``none``
  No highlighting. Use it to disable automatic highlighting
``ansi``
  ANSI escape codes for your shell
``html``
  HTML tags


====
Bugs
====
Does ``php-sqllint`` not detect a syntax error, or doesn't support a certain
SQL statement?
Then please report a bug at `phpmyadmin/sql-parser`__.

__ https://github.com/phpmyadmin/sql-parser


======================
Install using composer
======================
Run ``composer require cweiske/php-sqllint``, the package is hosted on `packagist <https://packagist.org/packages/cweiske/php-sqllint>`__


========
Download
========
The download files are equipped with all dependencies.
Just download and run.

.. LATESTRELEASE

See `php-sqllint downloads page <http://cweiske.de/php-sqllint-download.htm>`_
for all released versions.


============
Dependencies
============
- PEAR's `Console_Commandline`__
- `phpMyAdmin's SqlParser`__

__ http://pear.php.net/package/Console_CommandLine
__ https://github.com/phpmyadmin/sql-parser


Dependency installation
=======================
::

    $ composer install

Now you can use ``./bin/php-sqllint`` without building the phar yourself.


========
Building
========

Preparation
===========
1. Write new version number into ``VERSION``


Create the release
==================
You'll need `phing`__, the PHP build tool::

    $ phing

__ https://www.phing.info/

The result are ``.phar`` files in ``dist/`` directory that you can execute::

    $ ./dist/php-sqllint-0.0.1.phar tests/files/create-noname.sql 
    Checking SQL syntax of tests/files/create-noname.sql
     Line 1, col 12 at "(": The name of the entity was expected.


=================
About PHP-SQLlint
=================

License
=======
``php-sqllint`` is licensed under the `AGPL v3`__ or later.

__ http://www.gnu.org/licenses/agpl.html


Homepage
========
Home page
   http://cweiske.de/php-sqllint.htm
Source code
   http://git.cweiske.de/php-sqllint.git

   Mirror: https://github.com/cweiske/php-sqllint


Author
======
Written by `Christian Weiske`__, cweiske+php-sqllint@cweiske.de

__ http://cweiske.de/
