Setting up MySQL for SSL on Ubuntu
----------------------------------
Create a new directory `ssl` in your `/var/lib/mysql` directory. This is important, because AppArmor will blacklist certs outside of this particular directory.

If SSL support is compiled with MySQL (it usually is, if you've installed via `apt-get`), then you should have the `mysql_ssl_rsa_setup` command available. Run this command as root then change ownership to the mysql user:
```
mysql_ssl_rsa_setup -d /var/lib/mysql/ssl
chown -R mysql:mysql /var/lib/mysql/ssl
```

Add ssl.cnf to /etc/mysql/mysql.conf.d/ssl.cnf
```ini
[mysqld]
ssl-ca=/var/lib/mysql/ssl/ca.pem
ssl-cert=/var/lib/mysql/ssl/server-cert.pem
ssl-key=/var/lib/mysql/ssl/server-key.pem
```

Copy the CA and client components into a directory readable to you. They need to be readable by you, and probably your webserver user. 

```
cp /var/lib/mysql/ssl/ca.pem ~/.
cp /var/lib/mysql/ssl/client-cert.pem ~/.
cp /var/lib/mysql/ssl/client-key.pem ~/.

```

Distribute these three files to anyone who needs to connect to the server. You will need to use these particular certificates to connect through the MySQL client, as well as your usual username/password. 

At this point, connecting with SSL is still optional - anyone without the certs can use the usual username/password for unencrypted connections. 

Setting up MySQL for SSL on OSX
-------------------------------
(coming soon)
