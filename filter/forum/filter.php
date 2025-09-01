<?php

/**
 * Change all instances of forum in the text
 *
 * @uses $CFG,$COURSE;
 * Apply the filter to the text
 *
 * @see  filter_manager::apply_filter_chain()
 * @param string $text to be processed by the text
 * @param array $options filter options
 * @return string text after processing
 */
class_alias(\filter_forum\text_filter::class, \filter_forum::class);
