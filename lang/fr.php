<?php

/*
 * Interface translations (fr). Strings are grouped by area in lang/fr/*.php
 * and merged here; the application reads this single file.
 */
$lines = [];
foreach (glob(__DIR__ . '/fr/*.php') ?: [] as $file) {
    $lines += require $file;
}
return $lines;
