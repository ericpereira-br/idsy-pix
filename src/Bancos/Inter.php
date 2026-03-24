<?php

namespace Idsy\Pix\bancos;

use Idsy\Pix\Abstract\Banco;
use Idsy\Tools\Convert;
use Idsy\Tools\Validate;

class Inter extends Banco {
    public function __construct()
    {
        parent::__construct();
    }

    public function token() : void
    {   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://cdpj.partners.bancointer.com.br/oauth/v2/token");       
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
        curl_setopt($ch, CURLOPT_POSTFIELDS, 
            http_build_query(array('client_id' => $this->autenticacao->getClientId(), 
                                'client_secret' => $this->autenticacao->getClientSecret(), 
                                'scope' => 'cob.write cob.read pix.write pix.read',                                 
                                // 'scope' => $scope, //'cob.write, cob.read', 
                                'grant_type' => 'client_credentials')));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            
        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $server_response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close ($ch);

        if ($error !== '') {
            throw new \Exception($error);
        }

        if ($server_response == '') {
            throw new \Exception("Resposta vazia, provavelmente o limite de chamadas foi atingido...\n");
        }

        $obj = json_decode($server_response);

        if ((!isset($obj->{'access_token'}) or empty($obj->{'access_token'}))) {
            throw new \Exception($obj);
        }     

        $agora = new \DateTime(); // hora atual
        $agora->add(new \DateInterval('PT45M')); // soma 45 minutos
        $this->autenticacao->setAccessToken($obj->{'access_token'});
        $this->autenticacao->setTokenExpiraEm($agora);
    }

    public function gerar() :void
    {
        if ($this->validarToken()==false) {
            $this->token();
        }

        $auth='Authorization: Bearer ' . $this->autenticacao->getAccessToken();
        $cc='x-conta-corrente: '.$this->recebedor->getConta();
        $json='Content-Type: application/json'; 

        $devedor = '';
        if ($this->devedor->getCpfCnpj()!="") {
            $devedorCpfCnpj = $this->devedor->getCpfCnpj();
            $devedorNome = $this->devedor->getNome();

            $devedor = <<<DATA
            "devedor": 
            {
            "cnpj": "$devedorCpfCnpj",
            "nome": "$devedorNome"
            },
            DATA;
        }

        $valor = $this->detalhe->getValor();
        $chave = $this->recebedor->getChave();
        
        $solicitacaoPagador = $this->detalhe->getSolicitacaoPagador();        

        $infoAdicionais = '';
        if (($this->detalhe->getInfoAdicionais()>0) and (!empty($this->detalhe->getInfoAdicionais())) and ($this->detalhe->getInfoAdicionais()!=null)) {
            $infoAdicionais = '"infoAdicionais":'.json_encode($this->detalhe->getInfoAdicionais());
        }

        $data=<<<DATA
        { 
            "calendario": {
            "expiracao": 3600
            },
            $devedor
            "valor": {
            "original": "$valor",
            "modalidadeAlteracao": 1
            },
            "chave": "$chave",
            "solicitacaoPagador": "$solicitacaoPagador",
            $infoAdicionais
        }
        DATA;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/pix/v2/cob/".$this->detalhe->getTxid());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$cc,$json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$json));        
        curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close ($ch);

        if ($error !== '') {
            throw new \Exception($error);
        }

        if (Validate::json($result) == '') {
            throw new \Exception($result);
        }

        $jsonResult = json_decode($result);

        $status = Convert::jsonToChave($result, 'status');

        if ((!isset($status) or empty($status))) {
            throw new \Exception('Status não encontrado!');
        }

        $pixCopiaECola = Convert::jsonToChave($result, 'pixCopiaECola');

        if ((!isset($pixCopiaECola) or empty($pixCopiaECola))) {
            throw new \Exception('pixCopiaECola não encontrado!');
        }

        // if ((!isset($jsonResult->{'status'}) or empty($jsonResult->{'status'}))) {
        //     throw new \Exception(json_encode($jsonResult));
        // }

        // if ((!isset($jsonResult->{'pixCopiaECola'}) or empty($jsonResult->{'pixCopiaECola'}))) {
        //     throw new \Exception(json_encode($jsonResult));
        // }   

        $this->detalhe->setStatus($status);
        $this->detalhe->setPixCopiaECola($pixCopiaECola);        
    }

    public function consultar() : void
    {
        if ($this->validarToken()==false) {
            $this->token();
        }
        $auth='Authorization: Bearer ' . $this->autenticacao->getAccessToken();
        $cc='x-conta-corrente: '.$this->recebedor->getConta();
        $json='Content-Type: application/json'; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/pix/v2/cob/".$this->detalhe->getTxid());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$cc,$json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$json));        
        curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close ($ch);

        if ($error !== '') {
            throw new \Exception($error);
        }

        if (Validate::json($result) == '') {
            throw new \Exception($result);
        }        

        $jsonResult = json_decode($result);        

        $this->detalhe->setId(Convert::jsonToChave($result, 'id'));        
        $this->detalhe->setTxid(Convert::jsonToChave($result, 'txid'));        
        $this->detalhe->setEndToEndId(Convert::jsonToChave($result, 'endToEndId'));                
        $this->detalhe->setStatus(Convert::jsonToChave($result, 'status'));                        
        $this->detalhe->setPixCopiaECola(Convert::jsonToChave($result, 'pixCopiaECola'));                                
        $this->detalhe->setValor(Convert::jsonToChave($result, 'original'));                                        
        $this->detalhe->setValorPago(Convert::jsonToChave($result, 'valor'));   

        

        // if ((!isset($jsonResult->loc->id) or empty($jsonResult->loc->id))) {
        //     throw new \Exception('ID não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setId($jsonResult->loc->id);
        // }        
        
        // if ((!isset($jsonResult->txid) or empty($jsonResult->txid))) {
        //     throw new \Exception('Txid não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setStatus($jsonResult->txid);
        // }  
        
        // if ((!isset($jsonResult->pix[0]->endToEndId) or empty($jsonResult->pix[0]->endToEndId))) {
        //     throw new \Exception('Txid não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setEndToEndId($jsonResult->pix[0]->endToEndId);
        // }          

        // if ((!isset($jsonResult->status) or empty($jsonResult->status))) {
        //     throw new \Exception('Status não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setStatus($jsonResult->status);
        // }

        // if ((!isset($jsonResult->pixCopiaECola) or empty($jsonResult->pixCopiaECola))) {
        //     throw new \Exception('PixCopiaECola não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setPixCopiaECola($jsonResult->pixCopiaECola);
        // }  

        // if ((!isset($jsonResult->valor->original) or empty($jsonResult->valor->original))) {
        //     throw new \Exception('Valor não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setValor($jsonResult->valor->original);
        // }

        // if ((!isset($jsonResult->pix[0]->valor) or empty($jsonResult->pix[0]->valor))) {
        //     throw new \Exception('Valor não encontrada em: ' . $result);
        // }else{
        //     $this->detalhe->setValorPago($jsonResult->pix[0]->valor);
        // }

        if ((isset($jsonResult->pix[0]->devolucoes)) or (!empty($jsonResult->pix[0]->devolucoes))) {
            $valorDevolucao = 0;

            foreach ($jsonResult->pix[0]->devolucoes as $devolucao) {
                $valorDevolucao += $devolucao->valor;
            }

            $this->detalhe->setValorDevolucao($valorDevolucao);
            $this->detalhe->devolucoes = $jsonResult->pix[0]->devolucoes;
        }       
        
        if ((isset($jsonResult->infoAdicionais)) and (!empty($jsonResult->infoAdicionais))) {
            $this->detalhe->infoAdicionais = $jsonResult->infoAdicionais;
        }        

        if ((!isset($jsonResult->calendario->criacao) or empty($jsonResult->calendario->criacao))) {
            throw new \Exception('Pix sem data de criação!');
        }

        if ((!isset($jsonResult->calendario->expiracao) or empty($jsonResult->calendario->expiracao))) {
            throw new \Exception('Pix sem data de expiração!');
        }
        
        if (mb_strtoupper($this->detalhe->getStatus())<>'CONCLUIDA'){        
            if (Validate::pix($jsonResult->calendario->criacao, $jsonResult->calendario->expiracao) == false){
                $this->detalhe->setStatus('expirado');
            }    
        }
    }

    public function pagar() : void
    {
        if ($this->validarToken()==false) {
            $this->token();
        }
        $auth='Authorization: Bearer ' . $this->autenticacao->getAccessToken();
        $cc='x-conta-corrente: '.$this->recebedor->getConta();
        $json='Content-Type: application/json'; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/pix/v2/cob/".$this->detalhe->getTxid());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$cc,$json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$json));        
        curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close ($ch);

        if ($error !== '') {
            throw new \Exception($error);
        }

        $jsonResult = json_decode($result);
        $this->detalhe->setStatus($jsonResult->status);
        $this->detalhe->setPixCopiaECola($jsonResult->pixCopiaECola);        
    }
}