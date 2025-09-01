# In-Place Custom Translate #

Moodle extension. Edit language strings in-place right on your page.

Install plugin as usual. 

After installation create a new hook in core file: /lib/classes/string_manager_standard.php 
Line ~ 200, before the line with:
```
// We do not want any extra strings from other languages - everything must be in en lang pack.
```
add these lines:
```
$cstring = [];
$deps = $this->get_language_dependencies($lang);
array_unshift($deps, "en");
foreach ($deps as $dep) {
    if ($pluginswithfunction = get_plugins_with_function('custom_language_translation', 'lib.php')) {
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $function($cstring, $component, $dep);
            }
        }
    }
}
$string = array_merge($string, $cstring);
```

Tool is only available for the Manager or higher roles.

Site administration / Development / Debugging - Debugging message should be DEVELOPER

Add ?strings=1 to the page url to activate the language tool.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/admin/tool/inplacetranslate

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2021 Devlion <info@devlion.co>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
