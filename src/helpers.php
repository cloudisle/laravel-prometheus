<?php

function is_associative_array(array $array): bool
{
    if (empty($array)) {
        return false;
    }

    // Check if the array has non-numeric keys
    // Found a non-numeric key, it's associative
    return array_any($array, fn($key) => !is_int($key));
}