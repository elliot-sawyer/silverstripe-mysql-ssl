<?php

namespace ElliotSawyer\SSLMySQLDatabase;

use SilverStripe\Core\Config\Config;
use mysqli;
use mysqli_stmt;
use SilverStripe\ORM\Connect\MySQLiConnector;

/**
 * Connector for MySQL using the MySQLi method using SSL
 */
class SSL_MySQLiConnector extends MySQLiConnector
{
    /**
     * This will automatically attempt to verify the CA certificate authority
     * For security reasons this defaults to true.
     *
     * Set this to false if you are using self-signed certs.
     *
     * @var [type]
     */
    private static $verify_ssl_certificate = true;

    public function connect($parameters, $selectDB = false)
    {
        // Normally $selectDB is set to false by the MySQLDatabase controller, as per convention
        $selectedDB = ($selectDB && !empty($parameters['database'])) ? $parameters['database'] : null;

        // Connection charset and collation
        $connCharset = Config::inst()->get(MySQLDatabase::class, 'connection_charset');
        $connCollation = Config::inst()->get(MySQLDatabase::class, 'connection_collation');

        $this->dbConn = mysqli_init();

        // Use native types (MysqlND only)
        if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
            $this->dbConn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);

        // The alternative is not ideal, throw a notice-level error
        } else {
            user_error(
                'mysqlnd PHP library is not available, numeric values will be fetched from the DB as strings',
                E_USER_NOTICE
            );
        }

        // Set SSL parameters if they exist. All parameters are required.
        if (array_key_exists('ssl_key', $parameters) &&
            array_key_exists('ssl_cert', $parameters) &&
            array_key_exists('ssl_ca', $parameters)) {
            $this->dbConn->ssl_set(
                $parameters['ssl_key'],
                $parameters['ssl_cert'],
                $parameters['ssl_ca'],
                dirname($parameters['ssl_ca']),
                array_key_exists('ssl_cipher', $parameters)
                    ? $parameters['ssl_cipher']
                    : self::config()->get('ssl_cipher_default')
            );
        }

        $this->dbConn->real_connect(
            $parameters['server'],
            $parameters['username'],
            $parameters['password'],
            $selectedDB,
            !empty($parameters['port']) ? $parameters['port'] : ini_get("mysqli.default_port"),
            null,
            $this->config()->verify_ssl_certificate
                ? null
                : MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
        );

        if ($this->dbConn->connect_error) {
            $this->databaseError("Couldn't connect to MySQL database | " . $this->dbConn->connect_error);
        }

        // Set charset and collation if given and not null. Can explicitly set to empty string to omit
        $charset = isset($parameters['charset'])
                ? $parameters['charset']
                : $connCharset;

        if (!empty($charset)) {
            $this->dbConn->set_charset($charset);
        }

        $collation = isset($parameters['collation'])
            ? $parameters['collation']
            : $connCollation;

        if (!empty($collation)) {
            $this->dbConn->query("SET collation_connection = {$collation}");
        }
    }
}
