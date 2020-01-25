<?php
namespace ElliotSawyer\SSLMySQLDatabase;

use SilverStripe\Core\Environment;
use SilverStripe\ORM\Connect\MySQLDatabase;

class SSLMySQLDatabase extends MySQLDatabase
{
    public function connect($parameters, $selectedDB = false)
    {
        $envMap = [
            'SS_DATABASE_SSL_KEY' => 'ssl_key',
            'SS_DATABASE_SSL_CERT' => 'ssl_cert',
            'SS_DATABASE_SSL_CA' => 'ssl_ca',
        ];

        // Loop through the list of possible SSL DB environment variables
        foreach ($envMap as $envKey => $paramKey) {
            if ($val = Environment::getEnv($envKey)) {
                $parameters[$paramKey] = $val;
            }
        }

        return parent::connect($parameters, $selectedDB);
    }
}
