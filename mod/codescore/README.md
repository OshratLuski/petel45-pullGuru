# Codescore #

## Introduction ##
Codescore plugin allows teachers to get second opinion on student's code in case they missed something. Just write code task, select programming language and when student submits solution -  Codescore will grade it.

## Requirements ##

In order to use Codescore you need to have one of the following:
1. Moodle version 4.1
2. Moodle version 4.2
3. Moodle version 4.3

Moreover, the plugin requires PHP version of 8.1 and above.
As well, the plugin is working with SQL queries, therefore is compatible to MySQL-based DB's (such as MySql, MariaDB e.t.c).

## Installation ##
### Installing via uploaded ZIP file ###

1. Log in to your Moodle site as an admin and go to Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

### Installing manually ###

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/codescore

Afterwards, log in to your Moodle site as an admin and go to Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

### Installing with git ###

Use
    $ git clone { this repository URL/git address}

Afterwards, log in to your Moodle site as an admin and go to Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Managing Pages ##
You may find the plugin admin settings under Site administration -> Plugins -> Activity plugins -> Code Score

## Technical Support ##
If you have questions or need help integrating Code Score, please contact us (Support@devlion.co) instead of opening an issue


## License ##

2024 Devlion <info@devlion.co>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.