<?php

function calculate_group_display_number(int $base_group_number, int $entry_year): string
{
    $current_year = 2025;
    $course = $current_year - $entry_year + 1;
    $display_course = min($course, 4);
    return $display_course . str_pad($base_group_number, 2, '0', STR_PAD_LEFT);
}
