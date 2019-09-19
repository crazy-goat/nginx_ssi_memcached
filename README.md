# Nginx + SSI + memcached + PHP
This docker show how to configure `Nginx` with `SSI` (Server Side
Include). Additionally some `PHP` results can stored in `Memcached` and
reused by Niginx. More information how it work see section Details.

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
docker build --no-cache -t crazygoat/nginx_ssi_memcached .
```

## Details

TODO

![Image of Yaktocat](https://octodex.github.com/images/yaktocat.png)