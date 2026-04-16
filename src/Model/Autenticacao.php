<?php

namespace Idsy\Pix\Model;

use DateTime;

class Autenticacao
{
    private string $certificado;
    private string $chave;
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private DateTime $tokenExpiraEm;

    public function __construct()
    {
        $this->toClear();
    }

    public function toClear()
    {
        $this->certificado      = "";
        $this->chave            = "";
        $this->clientId         = "";
        $this->clientSecret     = "";
        $this->accessToken      = "";
        $this->tokenExpiraEm    = new DateTime();
    }

    /**
     * Mostro o directorio do certificado. 
     * @return string    
     */    
    public function getCertificado() : string
    { 
        return $this->certificado; 
    }

    /**
     * Salva o directorio do certificado.     
     * @param string $value é o directorio do certificado
     * @return void
     */    
    public function setCertificado(string $value)
    { 
        if (!file_exists($value)) {
            throw new \Exception("Certificado não encontrado em: " . $value);
        }
        $this->certificado = $value; 
    }

    /**
     * É a chave pix da conta principal.
     * @return string
     */    
    public function getChave() : string
    { 
        return $this->chave; 
    }

    /**
     * Altera ou inclui a chave pix da conta principal.
     *
     * @param string $value é a chave pix
     * @return void
     */    
    public function setChave(string $value)
    { 
        if (!file_exists($value)) {
            throw new \Exception("Chave não encontrada em: " . $value);
        }
        $this->chave = $value; 
    }

    /**
     * É o clientId da api pix.
     *
     * @param string $value é a chave pix
     * @return string
     */        
    public function getClientId() : string
    { 
        return $this->clientId; 
    }

    /**
     * Altera ou inclui o clientId da api pix.
     *
     * @param string $value
     * @return void
     */     
    public function setClientId(string $value)
    {
        $this->clientId = $value;
    }

    /**
     * É o clientSecret da api pix.
     *
     * @param string $value
     * @return string
     */     
    public function getClientSecret() : string
    { 
        return $this->clientSecret; 
    }

    /**
     * altera ou inclui o clientSecret da api pix.
     *
     * @param string $value 
     * @return void
     */     
    public function setClientSecret(string $value)
    {
        $this->clientSecret = $value;
    }

    /**
     * É o Token da api pix, capturado pela aplicação.
     *     *
     * @return string
     */     
    public function getAccessToken() : string
    { 
        return $this->accessToken; 
    }

    /**
     * altera ou inclui o accessToken da api pix.
     *
     * 
     * @return void
     */ 
    public function setAccessToken(string $value)
    {
        $this->accessToken = $value;
    }

    /**
     * É o Validade do token da api pix, capturado pela aplicação.
     *
     * @return datetime
     */         
    public function getTokenExpiraEm() : DateTime
    { 
        return $this->tokenExpiraEm; 
    }

    /**
     * altera ou inclui o validade do token da api pix.
     *
     * @param datetime $value
     * @return void
     */     
    public function setTokenExpiraEm(DateTime $value)
    {
        $this->tokenExpiraEm = $value;
    }
}