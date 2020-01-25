<?php
namespace ElliotSawyer\SSLMySQLDatabase;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\ORM\DB;

if (!interface_exists(EnvironmentCheck::class)) return;

class SSLCheck implements EnvironmentCheck
{
    public function check()
    {
        $q = DB::query("SHOW STATUS LIKE '%ssl_cipher'");
        $status = $q->nextRecord();

        if (isset($status['Variable_name']) && isset($status['Value'])) {
            $variableName = $status['Variable_name'];
            $value = $status['Value'];

            if($variableName === 'Ssl_cipher' || $variableName === 'ssl_cipher') {
                return strlen($value) > 0
                    ? [EnvironmentCheck::OK, 'OK']
                    : [EnvironmentCheck::ERROR, 'FAIL'];
            }
        }

        return [EnvironmentCheck::WARNING, 'MySQL is connected but using an insecure connection'];
    }
}
