<?php

namespace Idsy\Pix\Bancos;

interface BancoInterface{
    public function getResult(): string;
    public function setResult(string $value): void;
    public function validarToken(): bool;
    public function token(): void;
    public function gerar(): void;
    public function consultar(): void;    
    public function pagar(): void;    
}