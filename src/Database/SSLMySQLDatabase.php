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
            'SS_DATABASE_SSL_CIPHER' => 'ssl_cipher'
        ];

        // Loop through the list of possible SSL DB environment variables
        foreach ($envMap as $envKey => $paramKey) {
            $val = Environment::getEnv($envKey);

            // if these values are specifically the case-sensitive string "NULL",
            // we may need to pass null values into the database connection string
            // but we can't use "NULL" because dotenv parses it as a string
            if ($val === 'NULL') {
                $parameters[$paramKey] = null;
            } elseif ($val) {
                $parameters[$paramKey] = $val;
            }
        }

        return parent::connect($parameters, $selectedDB);
    }
}
