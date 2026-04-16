<?php

require '../vendor/autoload.php';

use Idsy\Pix\Pix;
// 
// cria um objeto para o banco especifico
$pix = new Pix('077');

// carrega os dados do recebedor
$pix->banco->recebedor->setConta('264083320');
$pix->banco->recebedor->setChave('44048508000199');

// carrega os dados de autenticacao
$pix->banco->autenticacao->setClientSecret('151sdfd21-ab52-4f0a-832b-9adfervdef1c');
$pix->banco->autenticacao->setClientId('154811a4-9716-462e-sdf5-82adervhb89e6');
$pix->banco->autenticacao->setChave('src/storage/certificados/key.key');
$pix->banco->autenticacao->setCertificado('src/storage/certificados/crt.crt');

$pix->banco->token('cob.read');
echo $pix->banco->autenticacao->getAccessToken();


