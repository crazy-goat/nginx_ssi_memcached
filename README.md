# Nginx + SSI + memcached + PHP
This docker show how to configure `Nginx` with `SSI` (Server Side
Include). Additionally some `PHP` results can stored in `Memcached` and
reused by Niginx. More information how it work see section Details.

## Demo
Working demo can be found [here](https://demo-nginx-ssi.crazy-goat.com/)

## Run
First run docker: 

```shell
docker run --rm  --name nginx_ssi -p 9999:80 crazygoat/nginx_ssi_memcached
```

next visit [http://127.0.0.1:9999](http://127.0.0.1:9999) and check 
time.

## Build
To build locally this docker just run command below:

```shell
docker build --rm -t crazygoat/nginx_ssi_memcached .
```

## Details
Our goal is to enable ssi in nginx and cache content served from
php in memcached. See diagram below how it works:


#### Request flow
![Nginx SSI](https://raw.githubusercontent.com/crazy-goat/nginx_ssi_memcached/master/docs/nginx-ssi.png)

Workflow:
1. Clients send request to nginx.
1. Nginx parse index.html and send request for SSI sub-request.
1. If sub-request is in memcached it is served from memcached
1. If sub-request is not in memcached it is passed to php-fpm 
1. Php-fpm executes script and sets cache.

#### Enabling SSI
SSI in nginx is disabled by default, so we need to enable it. We can do this
by adding `ssi on;` in nginx configuration file in `server` or `location` block.

```
server {
    ssi on;
    ...
} 
```

No every response is check for valid ssi command. For example:
```html
<p class="lead">Time: <!--# include virtual="/time.php" --></p>
``` 

Nginx will make sub-request for `/time.php` and insert response between
`<!--#` and `-->`.


#### Enabling memcached
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

If $memcached_key not exists in cache, memcached return 404 status code.
If there is some connection problem between nginx and memcached 502 is returned. 
Errors 404 and 502 are handled by @fallback section - standard php-fpm call.

#### Passing $memcached_key to PHP
For cache key consistence between nginx and PHP, 
we can pass key to php-fpm using `fastcgi_param`.

```
    location @fallback {
        # Pass memcache key as header to PHP
        fastcgi_param  HTTP_X_MEMCACHED_KEY $memcached_key;
        ...
    }
```
In PHP script it can be accessed through variable `$_SERVER['HTTP_X_MEMCACHED_KEY']`.

#### Saving response to cache
Script below prints time. If `x-memcached-key` header is set and `time`
query param exists then response is stored in memcached for `time` seconds. 

```php
<?php
$data = (new DateTime())->format('Y-m-d H:i:s');
echo $data;

// store respone in memcached
if (isset($_SERVER['HTTP_X_MEMCACHED_KEY']) && isset($_GET['time'])) {
    $mem = new Memcached('nginx_memcached');
    $mem->addServer("127.0.0.1", 11211);
    $mem->set(
        $_SERVER['HTTP_X_MEMCACHED_KEY'],
        $data,
        time() + (int)$_GET['time']
    );
}
```

#### Page structure

![SSI Components](https://raw.githubusercontent.com/crazy-goat/nginx_ssi_memcached/master/docs/ssi-components.png)
