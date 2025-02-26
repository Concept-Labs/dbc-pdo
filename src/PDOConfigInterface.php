<?php
namespace Concept\DBC\PDO;

interface PDOConfigInterface extends \Concept\Config\ConfigInterface
{
    const DSN = 'dsn';

    const DRIVER = 'driver';
    const HOST = 'host';
    const PORT = 'port';
    const DATABASE = 'database';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const OPTIONS = 'options';
    const CHARSET = 'charset';
    
}