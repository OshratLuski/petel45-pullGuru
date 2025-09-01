## Tutorials custom functions

Update Weizmann HR via ESB WS when a user completes an
activity.

### Configuration

moodle-instance/admin/settings.php?section=local_tutorials
* Set ESB WS URL, username & password
  * Testing: https://esb-test.weizmann.ac.il/esb/rest/GC_INSERT_PASSED_MDL_TEST_HR
  * Production: https://esb.weizmann.ac.il/esb/rest/GC_INSERT_PASSED_MDL_TEST_HR
* Set activities CMID that we are observing

moodle-instance/admin/settings.php?section=http
* $CFG->proxybypass .= hswdwsv01t.weizmann.ac.il, ibwwsdmz.weizmann.ac.il

