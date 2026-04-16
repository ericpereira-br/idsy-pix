<?php

namespace Idsy\Pix\Model;

class Recebedor
{
    private string $conta;
    private string $chave;
    private string $tipoChave;

    public function __construct()
    {        
        $this->toClear();
    }

    public function toClear(): void
    {
        $this->conta               = "";
        $this->chave               = "";
        $this->tipoChave           = "";
    }

    public function getConta() : string
    { 
        return $this->conta; 
    }

    public function setConta(string $value)
    { 
        $this->conta = $value; 
    }

    public function getChave() : string
    { 
        return $this->chave; 
    }

    public function setChave(string $value)
    { 
        $this->chave = $value; 
    }

    public function getTipoChave() : string
    { 
        return $this->tipoChave; 
    }

    public function setTipoChave(string $value)
    {      
        $this->tipoChave = $value; 
    }
}