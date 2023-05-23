# xlsx2fac

Convert a XLS(X)/ODS/CSV-Speadsheet to Facto 5.3 import format.

1. clone the project 
2. install phpspreadsheet using composer (https://getcomposer.org/download/)

   <code>composer require phpoffice/phpspreadsheet</code>

3. add your own profiles to convert

## creating profiles

1. create a sample export from Facto

2. for looping split sample in two files, add in the file with the the static part a new Line at the end:

   <code>LOOP:${loop profil filename}[TAB]${row name for group}[TAB]${row name for sort}[ENTER]</code>
   
   in this case the whole spreadsheet is import. It's not for large files / check the memory limit in the php configuration.

3. change the values

Some Characters have a special meaning:

Character | Meaning | Example
--- | --- | ----
${ }  | text between ( ) is a variable from the actual speedsheet line. first line is uses as variable name|  ${Product Number}
!     | the following text is a SQL Statment returning a single value| select qgrp from art_0 where arnr = ${Product Number}
starting with text or numbers | static value | 0001
