#!/bin/bash

if [ $# -lt 1 ]; then
    echo "Usage: $0 <courses_file>"
    exit 1
fi

input_file="$1"

if [ ! -f "$input_file" ]; then
    echo "Error: File '$input_file' not found!"
    exit 1
fi

while IFS= read -r courseid
do
    echo "Processing Course ID: $courseid"
    php -f update_folder_name.php "$courseid"
done < "$input_file"
