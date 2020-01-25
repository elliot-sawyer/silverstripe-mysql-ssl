MySQL SSL support for SilverStripe 4
====================================

Why does this exist?
--------------------
Silverstripe 4 does not allow MySQL connections over SSL. The feature was removed during the early betas for unknown reasons and has not yet been restored. [An issue has been raised](https://github.com/silverstripe/silverstripe-framework/issues/8871) but remains unfixed. This module adds that support back in.

Configuration
-------------
Set the following variables in your .env
```ini
SS_DATABASE_CLASS="ElliotSawyer\SSLMySQLDatabase\SSLMySQLDatabase"
#this must be an IP address, localhost will not work
SS_DATABASE_SERVER="127.0.0.1"
SS_DATABASE_USERNAME="..."
SS_DATABASE_PASSWORD="..."
SS_DATABASE_SSL_KEY="/path/to/client-key.pem"
SS_DATABASE_SSL_CERT="/path/to/client-cert.pem"
SS_DATABASE_SSL_CA="/path/to/ca-cert.pem"
```

If the certificates and key are not defined, the database will connect without the SSL connection.

PDO will not work (see below) - you must use MySQLi. By default MySQLi will attempt verify your SSL certificate before connecting. You will need to disable this if you are using self-signed certificates.
```yml
ElliotSawyer\SSLMySQLDatabase\SSL_MySQLiConnector:
  verify_ssl_certificate: false
```

Localhost vs 127.0.0.1 vs IP address
------------------------------------
When your webserver and MySQL are on the same server, communication is done through socket connections and SSL is mostly irrelevant. MySQLi expects an IP address when using SSL, so use 127.0.0.1 in this case.

For connections over a network, you must use an IP address or database host as the hostname.

If you see an error `this stream does not support SSL/crypto`, this is the most likely cause.

More info: https://blog.machek.co.uk/2016/06/php-with-mysql-and-ssl.html

MySQLi vs PDO
-------------
When using PDO with SSL, PHP aborts with a "Trap 6" error. This is usually due to use of unverified certs, and may be related to non-existed hostnames in the CN attributes of the cert. A flag exists to override this behaviour but does not seem to work in some recent versions of PHP.

MySQLi has a similar issue, but its flag _does_ work. As such, this module requires you to use MySQLiConnector instead of PDOConnector. In practice, this should not be an issue because Silverstripe's ORM will handle database abstraction.


Verifying the SSL connection
----------------------------
You can verify that the secure connection is working by running the following SQL command: `DB::query("SHOW STATUS LIKE '%ssl_cipher'");`. If you are connected via SSL, you will get an array with one result: 
```
[
    Variable_name => Ssl_cipher,
    Value => DHE-RSA-AES256-SHA
]
```

If you have the [environmentcheck](https://github.com/silverstripe/silverstripe-environmentcheck) module installed, this is checked automatically and fails if an unsecured connection is used.

Contributing
------------
Contributions are more than welcome! Please raise some issues or create pull requests on the Github repo.

Support
--------
Need some extra help or just love my work? Consider shouting me a coffee or a small donation if this module helped you solve a problem. I accept cryptocurrency at the following addresses:
* Bitcoin: 12gSxkqVNr9QMLQMMJdWemBaRRNPghmS3p
* Bitcoin Cash: 1QETPtssFRM981TGjVg74uUX8kShcA44ni
* Litecoin: LbyhaTESx3uQvwwd9So4sGSpi4tTJLKBdz
* Ethereum: 0x0694E0704c70D8d178dd2e9522FC59EBBEe86748
