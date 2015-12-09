***********
PHP-SQLlint
***********

Command line tool to validate (syntax check) SQL files.
Primarily for MySQL ``.sql`` files.

Can be used in git pre-commit hooks to catch errors.


=====
Usage
=====
::

    $ ./bin/php-sqllint tests/files/create-missingcomma.sql 
    Checking SQL syntax of tests/files/create-missingcomma.sql
     Line 3, col 5 at "pid": A comma or a closing bracket was expected.
     Line 3, col 13 at "11": Unexpected beginning of statement.
     Line 3, col 17 at "DEFAULT": Unrecognized statement type.

Emacs mode::

    $ ./bin/php-sqllint -r emacs tests/files/create-noname.sql 
    tests/files/create-noname.sql:1.12:Error: The name of the entity was expected.
    tests/files/create-noname.sql:1.13:Error: A closing bracket was expected.
    tests/files/create-noname.sql:1.13:Error: At least one column definition was expected.


============
Dependencies
============
- PEAR's `Console_Commandline`__
- `udan11's SqlParser`__, which is used by `phpMyAdmin`__

__ http://pear.php.net/package/Console_CommandLine
__ https://github.com/udan11/sql-parser
__ https://www.phpmyadmin.net/




=================
About PHP-SQLlint
=================

License
=======
``php-sqllint`` is licensed under the `AGPL v3`__ or later.

__ http://www.gnu.org/licenses/agpl.html


Homepage
========
Source code
   http://git.cweiske.de/php-sqllint.git

   Mirror: https://github.com/cweiske/php-sqllint


Author
======
Written by Christian Weiske, cweiske@cweiske.de
