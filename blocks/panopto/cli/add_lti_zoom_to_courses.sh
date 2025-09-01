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

# Read and print each line from the file containing course IDs
while IFS= read -r courseid; do
    echo "Adding ZOOM LTI activity to courseid=$courseid"
    moosh -n activity-add --section 1 --name "LAUNCH ZOOM MEETING SESSION (LTI)" -o='--typeid=4 --resourcekey=123456 --password=secret' lti $courseid
done < "$COURSES_FILE"
