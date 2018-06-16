INTRODUCTION
------------

This module provides a node bulk action to export displayed
view rows to csv or xlsx file.


INSTALLATION
------------

Install as any other module.
For Excel export use composer to get PHPExcel library:
  composer require phpoffice/phpexcel 1.8.0
or download the library
from https://github.com/PHPOffice/PHPExcel/releases (version 1.8.1 has a bug)
and place it in /libraries/PHPExcel folder


REQUIREMENTS
------------

 * For Excel export the PHPExcel library is required
  (https://github.com/PHPOffice/PHPExcel)


USAGE
-----

Make sure that action is available in "Node operations bulk form"
field settings of your view.
