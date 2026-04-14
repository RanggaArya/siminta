<?php
echo "<pre>";

$path = __DIR__ . '/../artisan'; // naik satu folder ke root Laravel

passthru("php $path storage:link");

echo "</pre>";
