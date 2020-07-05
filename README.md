ZEN CART REMIBIT MODULE

## Integration Requirements

- A RemiBit merchant account
- Zen Cart, tested up to version 1.5.7 

INSTALLATION AND CONFIGURATION


In order to install the module, it is necessary to access the server where the web files are hosted by ssh. If you don’t know how to do that, please contact your site administrator or your hosting provider.

In this example we will use the default Zen Cart configuration, so the website files are located in /var/www/html/zencart and they are owned by the default user www-data. Please replace [zencart] with the actual name (if different) of your website directory and [www-data] with the owner (if different) of your web files directory.


## Installation

1/. Go to the Zen Cart directory

```
cd /var/www/html/zencart
```

2/. Fetch the RemiBit module

```
sudo -u www-data wget https://github.com/RemiBit/zencart-remibit/releases/download/v1.01/zencart-remibit.zip
```

3/. Uncompress it

```
sudo -u www-data unzip zencart-remibit.zip
```

Please make sure you are on your {WEBROOT} directory before uncompressing. From it, you should see extras and includes directories.

4/. Login to Admin dashboard, go to `Modules` > `Payment`, select `RemiBit Payment Method` from the list and click on the `Install Module` button 

  
5/. Fill up the RemiBit authentication information from your RemiBit merchant account's `Settings` > `Gateway`:

    * Login ID
    * Transaction Key
    * Signature Key
    * MD5 Hash Value

