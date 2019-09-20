<?php
declare(strict_types=1);

function cache_nginx(string $data, int $ttl = 3600): string
{
    if (isset($_SERVER['HTTP_X_MEMCACHED_KEY']) && $ttl > 0) {
        $mem = new Memcached('nginx_memcached');
        if (!count($mem->getServerList())) {
            $mem->addServer("127.0.0.1", 11211);
        }
        $mem->set(
            $_SERVER['HTTP_X_MEMCACHED_KEY'],
            $data,
            time() + $ttl
        );
    }
    return $data;
}