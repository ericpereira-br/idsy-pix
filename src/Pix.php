<?php

namespace Idsy\Pix;

use Idsy\Pix\Model\{
    Recebedor,
    Autenticacao,
    Devedor,
    Detalhe
};

use Idsy\Pix\Bancos\{
    Banco,
    BancoFactory,
    BancoInterface
};

class Pix implements BancoInterface
{
    private Banco $banco;
    public Autenticacao $autenticacao;
    public Recebedor $recebedor;
    public Devedor $devedor;
    public Detalhe $detalhe;    

    public function __construct(int $banco)
    {
        $this->banco = BancoFactory::create($banco);
        $this->autenticacao = $this->banco->autenticacao;
        $this->recebedor = $this->banco->recebedor;
        $this->devedor = $this->banco->devedor;
        $this->detalhe = $this->banco->detalhe;
    }   

    public function getResult(): string
    {
        return $this->banco->getResult();
    }

    public function setResult(string $value): void
    {
        $this->banco->setResult($value);
    }

    public function validarToken(): bool
    {
        return $this->banco->validarToken();
    }

    public function token(): void
    {
        $this->banco->token();
    }

    public function gerar(): void
    {
        $this->banco->gerar();
    }

    public function consultar(): void
    {
        $this->banco->consultar();
    }   

    public function pagar(): void
    {
        $this->banco->pagar();
    }        
}