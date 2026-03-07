<?php

namespace Idsy\Pix\abstract;

use Idsy\Pix\Model\Recebedor;
use Idsy\Pix\Model\Autenticacao;
use Idsy\Pix\Model\Devedor;
use Idsy\Pix\Model\Detalhe;

abstract class Banco{
    private string $result;
    public Autenticacao $autenticacao;
    public Recebedor $recebedor;
    public Devedor $devedor;
    public Detalhe $detalhe;

    public function __construct()
    {
        $this->recebedor = new Recebedor();
        $this->autenticacao = new Autenticacao();
        $this->devedor = new Devedor();
        $this->detalhe = new Detalhe();
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(string $value): void
    {
        $this->result = $value;
    }

    public function validarToken(): bool
    {
        $agora = new \DateTime(); // hora atual
        if ($agora > $this->autenticacao->getTokenExpiraEm()) {
            return false;
        }
        return true;        
    }    

    abstract public function token(): void;

    abstract public function gerar(): void;

    abstract public function consultar(): void;    

    abstract public function pagar(): void;    
}