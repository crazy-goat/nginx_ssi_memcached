# Nginx + SSI + memcached + PHP
This docker show how to configure `Nginx` with `SSI` (Server Side
Include). Additionally, some `PHP` results can be stored in `Memcached` and
reused by Nginx. More information how it works see section [Details](#details).

This demo is very simple and uses only raw php. It is no using any framework or`composer` dependencies.
**It is not production ready solution!**

## Live demo
Working demo can be found [here](https://nginx-ssi-memcached-ncz5ytyyqq-ew.a.run.app/)

## Docker images
To run this demo using docker run this command: 
```shell
docker run --rm  --name nginx_ssi -p 9999:80 crazygoat/nginx_ssi_memcached
```
then visit [http://127.0.0.1:9999](http://127.0.0.1:9999) in your favorite browser.

To build locally just run command below:
```shell
docker build --rm -t crazygoat/nginx_ssi_memcached .
```

## Details
Our goal is to enable `ssi` in `nginx` and optionally cache content from
php in `memcached` cache. Only content of `*.php` files is cached. 
Html files will be served directly from nginx without memcached lookup.

See diagram below how it works:

### Page structure
The page `index.html` consist of 4 SSI blocks: `head.html`, `menu.html`, `content.html` and `footer.html`
Additionally `content.html` include 2 blocks `time.php` one with cache and one without cache.
All SII blocks are placed in `_ssi` directory. This directory is marked as `internal` so
it can't be accessed via http request.

![SSI Components](https://raw.githubusercontent.com/crazy-goat/nginx_ssi_memcached/master/docs/ssi-components.png)

### Request flow
![Nginx SSI](https://raw.githubusercontent.com/crazy-goat/nginx_ssi_memcached/master/docs/nginx-ssi.png)

Workflow:
1. Clients send request to nginx.
2. Nginx parse `index.html` and make all necessary sub-request if any SSI tag is present.
3. Html files are included without cache lookup.
4. PHP files has additional check if key exist in memcached: 
   - If sub-request key exits in memcached it is served from memcached.
   - If sub-request key does not exist in memcached it is passed to `php-fpm`. 
   - `php-fpm` executes script send response content to memcached and nginx.
5. Nginx return `index.html` content to the client.

### Enabling SSI
SSI in nginx is disabled by default, so we need to enable it. We can do this
by adding `ssi on;` in nginx configuration file in `server` or `location` block.

```
server {
    ssi on;
    ...
} 
```

Now every response is checked for valid ssi command. For example:
```html
<p class="lead">Time: <!--# include virtual="/_ssi/time.php" --></p>
``` 

Nginx will make sub-request for `/_ssi/time.php` and the response replace comment tag
`<!--# include virtual="/_ssi/time.php" -->`.

### Enabling memcached
If nginx make sub-request for PHP script we have to check if response already exist
in memcached. If key exists we return content. If not (or some error) send
request to php-fpm. 

```
location ~ \.php$ {
    # set memcached key based on request
    set $memcached_key "nginx_ssi_memcached:$uri$is_args$args";
    
    # pass request to memcached
    memcached_pass 127.0.0.1:11211;
    memcached_socket_keepalive on;

    # grab error from memcached
    proxy_intercept_errors  on;

    # not found or error - running normal php
    error_page 404 502 = @fallback;
}
```

If `$memcached_key` does not exists in cache, memcached will return `404` status code.
If there is some connection problem between nginx and memcached `502` is returned. 
Errors `404` and `502` are handled by `@fallback` section - standard php-fpm call.

### Passing $memcached_key to PHP
To ensure cache key consistence between nginx and PHP, 
we pass key to php-fpm using `HTTP_X_MEMCACHED_KEY` fastcgi_param.

```
location @fallback {
    # Pass memcache key as header to PHP
    fastcgi_param HTTP_X_MEMCACHED_KEY $memcached_key;
    ...
}
```
In PHP script it can be accessed through variable `$_SERVER['HTTP_X_MEMCACHED_KEY']`.

### Saving response to cache
By default, script `time.php` prints current time. 
If there is `HTTP_X_MEMCACHED_KEY` set and `time`
param is present in query string then response is stored in memcached for `time` seconds. 

```php
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
```

### Hiding _ssi blocks
We do not want to show ssi block to the world. We want them to accessible only by nginx ssi sub-request.
To do tha we need set `internal` for all `/_ssi/*` requests:

```
location /_ssi/ {
    internal;
}
```
