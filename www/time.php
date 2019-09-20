<?php
declare(strict_types=1);
include_once "cache.php";

echo cache_nginx((new DateTime())->format('Y-m-d H:i:s'), (int)($_GET['time'] ?? 0));