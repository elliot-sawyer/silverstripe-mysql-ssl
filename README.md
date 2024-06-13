MySQL SSL support for SilverStripe 4
====================================

Why does this exist?
--------------------
Silverstripe 4 does not allow MySQL connections over SSL. The feature was removed during the early betas for unknown reasons and has not yet been restored. [An issue has been raised](https://github.com/silverstripe/silverstripe-framework/issues/8871) but remains unfixed. This module adds that support back in.

**Update**: This module is no longer needed for Silverstripe 4.13 and Silverstripe 5, and the above issue has now been resolved. This module will remain online for historical needs, as the documention provides context for setup on Azure

Installation
------------
`composer require elliotsawyer/silverstripe-mysql-ssl`

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

Microsoft Azure for MySQL users
-------------------------------
This module can be used to connect with Azure over a secure SSL connection. For security reasons, Azure enforces an SSL connection by default and strongly discourages you from turning it off. 

0. Before you begin: make sure your firewall settings are not blocking the connection from occurring. These rules can be configured in Azure
1. First, you must obtain a particular CA cert per the [Azure documentation](https://docs.microsoft.com/en-us/azure/mysql/howto-configure-ssl): https://www.digicert.com/CACerts/BaltimoreCyberTrustRoot.crt.pem
2. Save this certificate to a location accessible to your webserver, such as `/path/to/your/website/.well-known/BaltimoreCyberTrustRoot.crt.pem`
3. Add the following information to your .env file or environment variables
```ini
SS_DATABASE_USERNAME="YourAzureDBUsername"
SS_DATABASE_PASSWORD="YourAzureDBPassword"
SS_DATABASE_SERVER="YourAzureDBHostname.mysql.database.azure.com"
SS_DATABASE_SSL_KEY=NULL
SS_DATABASE_SSL_CERT=NULL
SS_DATABASE_SSL_CIPHER=NULL
SS_DATABASE_SSL_CA="/path/to/your/website/.well-known/BaltimoreCyberTrustRoot.crt.pem"
```
The module will convert the exact string "NULL" to a PHP `null` value for these specific database variables. Alternatively, instead of `SS_DATABASE_SSL_CIPHER`, you may define the following to a config.yml file:
```yml
SilverStripe\ORM\Connect\MySQLiConnector:
  ssl_cipher_default: null
```
The default cipher used by the MySQLiConnector class will not work with Azure. If you see an error `Abort trap: 6`, this is the most likely reason.

4. You're almost there! If you attempt to run a /dev/build now, you may see a message "Unknown database '<your_database_name>'". The good news is that Silverstripe has connected to your database over SSL. The other (bad?) news is that your database user provided to you by Azure may not have permissions to create a database, so you will be unable to create your specified database automatically. If this happens, you will need to log into Azure through some other means, such as the MySQL client, Sequel Pro, or MySQL Workbench to create the database manually:
```sql
CREATE DATABASE your_database_name
```
5. Run /dev/build again. If all goes well you should see CREATING DATABASE TABLES running slowly but successfully.

Contributing
------------
Contributions are more than welcome! Please raise some issues or create pull requests on the Github repo.

Acknowledgments
------------
Thanks to [maxime-rainville](https://github.com/maxime-rainville) and [obj63mc](https://github.com/obj63mc) for the original sample code that this module was built from.

Support
--------
Need some extra help or just love my work? Consider shouting me a coffee or a small donation if this module helped you solve a problem. I accept cryptocurrency at the following addresses:
* Bitcoin: 12gSxkqVNr9QMLQMMJdWemBaRRNPghmS3p
* Bitcoin Cash: 1QETPtssFRM981TGjVg74uUX8kShcA44ni
* Litecoin: LbyhaTESx3uQvwwd9So4sGSpi4tTJLKBdz
* Ethereum: 0x0694E0704c70D8d178dd2e9522FC59EBBEe86748
