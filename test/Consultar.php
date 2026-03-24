<?php

require '../vendor/autoload.php';

use Idsy\Pix\Pix;

// cria um objeto para o banco especifico
$pix = Pix::create('077');

// carrega os dados do recebedor
$pix->recebedor->setConta('264083320');
$pix->recebedor->setChave('44048508000199');

// carrega os dados de autenticacao
$pix->autenticacao->setClientSecret('151sdfd21-ab52-4f0a-832b-9adfervdef1c');
$pix->autenticacao->setClientId('154811a4-9716-462e-sdf5-82adervhb89e6');
$pix->autenticacao->setChave('src/storage/certificados/key.key');
$pix->autenticacao->setCertificado('src/storage/certificados/crt.crt');

// carrega os dados do pix
$pix->detalhe->setTxid('SYDI20251107A1B2C3D4E5F6G7H8I9J01');
$pix->consultar();
echo var_dump($pix->detalhe); 
