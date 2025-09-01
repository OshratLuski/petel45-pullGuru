#!/bin/bash

if [ $# -lt 1 ]; then
    echo "Usage: $0 <courses_file>"
    exit 1
fi

COURSES_FILE="$1"

if [ ! -f "$COURSES_FILE" ]; then
    echo "Error: File '$COURSES_FILE' not found!"
    exit 1
fi

while IFS= read -r courseid; do
    echo "Provisioning Panopto for courseid=$courseid"
    php /usr/share/nginx/html/moodle/feinberg/blocks/panopto/cli/provision_course_cli.php $courseid
done < "$COURSES_FILE"
