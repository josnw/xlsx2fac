# xlsx2fac

Convert a XLS(X)/ODS/CSV-Speadsheet to Facto 5.3 import format.

1. clone the project 
2. install phpspreadsheet using composer (https://getcomposer.org/download/)

   <code>composer require phpoffice/phpspreadsheet</code>

3. add your own profiles to convert

## creating profiles

1. create a sample export from Facto

2. for looping split sample in two files, add in the file with the the static part a new Line at the end:

   <code>LOOP:${path to loop profil filename}[TAB]${row name for group}[TAB]${row name for sort}</code>
   
   in case of a sort row the whole spreadsheet is import. It's not for large files / check the memory limit in the php configuration.
   you can use NOSORT as sortrow for large file without sorting

3. change the values

Some Characters have a special meaning:

Character | Meaning                                                                                            | Example
---       | ---                                                                                                | ----
${ }      | text between { } is a variable from the actual speedsheet line. first line is uses as variable name| ${Product Number}
!         | the following text is a SQL Statment returning a single value                                      | select qgrp from art_0 where arnr = ${Product Number}
?         | remember a previous value from data set                                                            | ?ARNR  
&STARTCOUNT | set a counter for loop to 0| &STARTCOUNT
&COUNT | add 1 to counter for loop | &COUNT
&CALC( )| parsing ${..} and ? and calculate the math expression between ( ) | &CALC( ${Price} * ?APJS )
starting with text or numbers | static value with parsing ${..} | 0001
