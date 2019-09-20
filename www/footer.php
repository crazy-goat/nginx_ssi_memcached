<?php
declare(strict_types=1);
include_once "cache.php";
ob_start();
?>
    <footer class="footer">
        <div class="container">
            <span class="text-muted"><a href="https://crazy-goat.com">Crazy Goat Software</a>  <?= date('Y') ?> - Nginx + SSI + cache example</span>
        </div>
    </footer>
<?php
$data = ob_get_clean();

$ttl = new DateTime('first day of january next year');
echo cache_nginx($data, $ttl->getTimestamp() - time());