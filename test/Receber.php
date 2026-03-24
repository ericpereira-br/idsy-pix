<?php

require '../vendor/autoload.php';

use Idsy\Pix\Pix;

// cria um objeto para o banco especifico
$pix = Pix::create('077');

// carrega os dados do recebedor
$pix->recebedor->setConta('264083320');
$pix->recebedor->setChave('44048508000199');

// tipo do conta 'C' = Conta Corrente, 'P' = Poupanca
$pix->recebedor->setTipoChave('C');

// carrega os dados de autenticacao
$pix->autenticacao->setClientSecret('151sdfd21-ab52-4f0a-832b-9adfervdef1c');
$pix->autenticacao->setClientId('154811a4-9716-462e-sdf5-82adervhb89e6');
$pix->autenticacao->setChave('src/storage/certificados/key.key');
$pix->autenticacao->setCertificado('src/storage/certificados/crt.crt');

$pix->detalhe->setTxid('SYDI00000000000000000000000000005');
$pix->detalhe->setValor('0.01');
$pix->detalhe->setSolicitacaoPagador('Testando PIX');
$pix->detalhe->addInfoAdicionais('Obra', 'ERIC PEREIRA 2');
$pix->detalhe->addInfoAdicionais('Colecionador', 'PATRICK PEREIRA 2');

$pix->gerar();
echo var_dump($pix->detalhe); 
