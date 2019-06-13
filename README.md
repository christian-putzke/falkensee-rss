Stadt Falkensee News RSS (inofficial)
==========================
This is a simple wrapper written in PHP which converts the news section of https://www.falkensee.de into an RSS 2.0 feed.

Version 1.0

Install
-------

In order to use this wrapper you need to install the dependency manager composer at first:

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

After that you need to resolve the dependencies using composer. Navigate to the folder where you've cloned this repository and execute this command:
```bash
composer install
```

Now you can execute the wrapper on the comandline:
```bash
php index.php
```
Alternatively you can point your webserver to the cloned repository and it should automatically call the index.php and render the feed. 
