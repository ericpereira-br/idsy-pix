<?php

namespace Idsy\Pix\Model;

use Idsy\Tools\Create;

class Detalhe
{
    private int $id;
    private string $txid;
    private string $endToEndId;
    private float $valor;
    private float $valorPago;
    private float $valorDevolucao;
    public null|array $devolucoes;
    private string $solicitacaoPagador;
    public null|array $infoAdicionais;
    private string $pixCopiaECola;
    private string $status;    
    private string $return;
    private string $returnAPI;

    public function __construct()
    {
        $this->toClear();
    }

    public function toClear(): void
    {
        $this->id                  = 0;
        $this->txid                = "";
        $this->endToEndId          = "";
        $this->valor               = 0;
        $this->valorPago           = 0;
        $this->valorDevolucao      = 0;
        $this->devolucoes          = [];
        $this->solicitacaoPagador  = "";
        $this->infoAdicionais      = [];
        $this->pixCopiaECola       = "";        
        $this->status              = "";
        $this->return              = "";
        $this->returnAPI           = "";        
    }

    public function getId() : string
    { 
        return $this->id; 
    }

    public function setId(int $value)
    { 
        $this->id = $value; 
    }

    /**
     * É um número sequencial que identifica o pagamento, gerado pelo cliente.
     * O banco inter o número precisa ter 33 caracteres e ter a primeira letra maiúscula.
     *
     * @return string
     */     
    public function getTxid() : string
    { 
        return $this->txid; 
    }
      
    public function setTxid(string $value)
    { 
        $this->txid = $value; 
    }       
     
    public function getEndToEndId() : string
    { 
        return $this->endToEndId; 
    } 

    public function setEndToEndId(string $value)
    { 
        $this->endToEndId = $value; 
    }

    /**
     * Altera ou inclui o txid.
     * É um número sequencial que identifica o pagamento, gerado pelo cliente.
     * O banco inter o número precisa ter 33 caracteres e ter a primeira letra maiúscula.
     * sigla é o prefixo do txid
     * seguencia é o numero sequencial
     *
     * @param string $sigla
     * @param int $seguencia
     * @return void
     */             
    public function createTxid(string $sigla, int $seguencia)
    {         
        $this->txid = Create::txid($sigla, $seguencia);        
    }

    public function getValor() : string
    {       
        return number_format($this->valor, 2, '.', ''); 
    }

    public function setValor(float $value)
    { 
        $this->valor = $value;
    }

    public function getValorPago() : string
    {       
        return number_format($this->valorPago, 2, '.', ''); 
    }

    public function setValorPago(float $value)
    { 
        $this->valorPago = $value;
    }    

    public function getValorDevolucao() : string
    {       
        return number_format($this->valorDevolucao, 2, '.', ''); 
    }

    public function setValorDevolucao(float $value)
    { 
        $this->valorDevolucao = $value;
    }        

    /**
     * Mensagem principal para o pagador.
     *
     * @return string
     */         
    public function getSolicitacaoPagador() : string
    { 
        return $this->solicitacaoPagador; 
    }

    /**
     * Altera ou inclui a solicitacaoPagador.
     * Mensagem principal para o pagador.
     *
     * @return void
     */             
    public function setSolicitacaoPagador(string $value)
    { 
        $this->solicitacaoPagador = mb_substr($value, 0, 140); 
    }

    /**
     * Detalhe do pagamento para o pagador.
     *
     * @return string
     */             
    public function getInfoAdicionais() : array
    { 
        return $this->infoAdicionais; 
    }
  
    public function addInfoAdicionais(string $nome, string $valor) { 
        $this->infoAdicionais[] = [
            "nome" => $nome,
            "valor" => $valor
        ];
    }

    public function getPixCopiaECola() : string
    { 
        return $this->pixCopiaECola; 
    }

    /**
     * Altera ou inclui o pixCopiaECola.
     * Codigo usado para pagar o pix.
     *
     * @param string $value
     * @return void
     */       
    public function setPixCopiaECola(string $value)
    { 
        $this->pixCopiaECola = $value; 
    }    

    /**
     * Status do pagamento.
     * PENDENTE
     * ATIVA
     * CONCLUIDA
     * REMOVIDA_PELO_USUARIO_RECEBEDOR
     * REMOVIDA_PELO_PSP
     *
     * @return string
     */
    public function getStatus() : string
    { 
        return $this->status; 
    }

    /**
     * Altera ou inclui o status.
     * Status do pagamento.
     * PENDENTE
     * ATIVA
     * CONCLUIDA
     * REMOVIDA_PELO_USUARIO_RECEBEDOR
     * REMOVIDA_PELO_PSP
     *
     * @param string $value
     * @return void
     */    
    public function setStatus(string $value)
    { 
        $this->status = $value; 
    }

    /**
     * Codigo usado para pagar o pix.
     *
     * @return string
     */     

    public function getReturn() : string
    { 
        return $this->return;
    }

    public function setReturn(string $value)
    { 
        $this->return = $value; 
    }

    public function getReturnAPI() : string
    { 
        return $this->returnAPI;
    }

    public function setReturnAPI(string $value)
    { 
        $this->returnAPI = $value; 
    }
}