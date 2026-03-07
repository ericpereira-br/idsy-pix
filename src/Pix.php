<?php

namespace Idsy\Pix;

use Idsy\Pix\Abstract\Banco;

use Idsy\Pix\Bancos\{
    Inter
};

class Pix
{
    public static function create(string $banco): Banco
    {
        if ($banco == "077") {
            return new Inter;
        } else {
            throw new \Exception('Banco não configurado!');                                 
        }
    }
}