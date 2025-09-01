# local PETEL #

This local plugin is a collections of various custom code patches
relevant only to the PETEL project.

## detailed information per patch ##

### PTL-7387 per user session timeout ###
Update config.php with the following settings:

Set Moodle core sessiontimeout to 365 days:

$CFG->sessiontimeout = 365 * 24 * 60 * 60;

Set PETEL's default sessiontimeout_user to 2 hours
(for all users that do not choose their own session timeout)

$CFG->sessiontimeout_user = 2 * 60 * 60;

## License ##

Department of science teaching,
Weizmann institute of science, Israel.

Contact: nadav.kavalerchik@weizmann.ac.il

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
