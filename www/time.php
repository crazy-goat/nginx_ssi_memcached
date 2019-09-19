<?php 
$data = (new DateTime())->format('Y-m-d H:i:s');
echo $data;

if (isset($_SERVER['HTTP_X_MEMCACHED_KEY']) && isset($_GET['time'])) {
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);
$mem->set($_SERVER['HTTP_X_MEMCACHED_KEY'], $data, time() + (int)$_GET['time']);
}
