<?php // versão 01;   
namespace Idsy\Pix\Model;

    class Devedor    
    {
        private string $nome;        
        private string $cpf_cnpj;

        public function toClear(): void
        {                 
            $this->nome                        = '';             
            $this->cpf_cnpj                    = '';
        }  
       
        public function __construct() 
        {
            $this->toClear();            
        }

        public function getNome(): string
        {
            return $this->nome;
        }

        public function setNome(string $value): void
        {
            $this->nome = mb_substr($value, 0, 50);
        }

        public function getCpfCnpj(): string
        {
            return $this->cpf_cnpj;
        }

        public function setCpfCnpj(string $value): void
        {
            $this->cpf_cnpj = mb_substr($value, 0, 14);
        }
  }        
?>