<?php

use ElliotSawyer\SSLMySQLDatabase\SSLCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;

if (class_exists(EnvironmentCheckSuite::class)) {
    EnvironmentCheckSuite::register('check', SSLCheck::class, 'Is MySQL using an SSL connection?');
}
