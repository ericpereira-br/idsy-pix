<?php

require '../vendor/autoload.php';

use Idsy\Pix\Pix;

// cria um objeto para o banco especifico
$pix = new Pix('077');

// carrega os dados do recebedor
$pix->banco->recebedor->setConta('264083320');
$pix->banco->recebedor->setChave('44048508000199');

// tipo do conta 'C' = Conta Corrente, 'P' = Poupanca
$pix->banco->recebedor->setTipoChave('C');

// carrega os dados de autenticacao
$pix->banco->autenticacao->setClientSecret('151sdfd21-ab52-4f0a-832b-9adfervdef1c');
$pix->banco->autenticacao->setClientId('154811a4-9716-462e-sdf5-82adervhb89e6');
$pix->banco->autenticacao->setChave('src/storage/certificados/key.key');
$pix->banco->autenticacao->setCertificado('src/storage/certificados/crt.crt');

$pix->banco->detalhe->setTxid('SYDI00000000000000000000000000005');
$pix->banco->detalhe->setValor('0.01');
$pix->banco->detalhe->setSolicitacaoPagador('Testando PIX');
$pix->banco->detalhe->addInfoAdicionais('Obra', 'ERIC PEREIRA 2');
$pix->banco->detalhe->addInfoAdicionais('Colecionador', 'PATRICK PEREIRA 2');

$pix->banco->gerar();
echo var_dump($pix->banco->detalhe); 
