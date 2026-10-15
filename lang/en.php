<?php

/*
 * Interface translations (en). Strings are grouped by area in lang/en/*.php
 * and merged here; the application reads this single file.
 */
$lines = [];
foreach (glob(__DIR__ . '/en/*.php') ?: [] as $file) {
    $lines += require $file;
}
return $lines;
