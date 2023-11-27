<?php declare(strict_types=1);
function cache_nginx(string $data, int $ttl = 3600): string
{
    $key = $_SERVER['HTTP_X_MEMCACHED_KEY'] ?? null;
    if ($ttl <= 0 || !is_string($key)) {
        return $data;
    }

    $mem = new Memcached();
    $mem->addServer("127.0.0.1", 11211);
    $mem->set($key, $data, time() + $ttl);

    return $data;
}

echo cache_nginx((new DateTime())->format('Y-m-d H:i:s'), intval($_GET['time'] ?? 0));
