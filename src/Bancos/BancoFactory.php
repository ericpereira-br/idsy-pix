<?php

namespace Idsy\Pix\Bancos;

use Idsy\Tools\Convert;

class BancoFactory
{
    private static array $map = [
        77  => Inter::class
    ];

    public static function create(string $codigo): Banco
    {
        if (!isset(self::$map[Convert::onlyNumber($codigo)])) {
            throw new \Exception('Banco não configurado!');                                 
        }

        $class = self::$map[$codigo];

        return new $class();
    }    
}