Mlnlpessay - is the question type plugin that executes python script based on mashine learning.
Mlnlpessay will execute adhoc task after student will submit his question. For each submition adhoc will exceute python script.

Parametters for config.php:
$CFG->mlnlpdebug - debug mode which will save all temp files to moodledata directory ($CFG->dataroot."/mlnlpdata")
$CFG->vendor_aws_path = ‘/usr/share/..../vendor’ - add to config path to aws lambda library
#https://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-lambda.html

Before using LAMDA set access to IAM User wich will execute LAMBDA Functions:
processing_mode -> AWS Lambda
aws_labmda_key -> key
aws_labmda_secret -> secret
aws_labmda_region -> region
aws_labmda_functionname -> function name

12/12/2022
added script to rerender attempt
php question/type/mlnlpessay/run.php [--cmid]
example:
php question/type/mlnlpessay/run.php --cmid=1222


04/05/2023
convert all files to LF format
update to version 4.1
