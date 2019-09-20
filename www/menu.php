<?php
declare(strict_types=1);
include_once "cache.php";
ob_start();
?>

<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <a class="navbar-brand" href="#">Nginx SSI test</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://github.com/crazy-goat/nginx_ssi_memcached"
                   target="_blank">Code</a>
            </li>
        </ul>
    </div>
</nav>


<?= cache_nginx(ob_get_clean());