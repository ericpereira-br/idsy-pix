<?php

namespace Idsy\Pix\bancos;

use Idsy\Pix\Abstract\Banco;
use Idsy\Tools\Convert;
use Idsy\Tools\Create;
use Idsy\Tools\Validate;

class Inter extends Banco
{
    public function __construct()
    {
        parent::__construct();
    }

    public function token(): void
    {
        try {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/oauth/v2/token");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
            curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                http_build_query(array(
                    'client_id' => $this->autenticacao->getClientId(),
                    'client_secret' => $this->autenticacao->getClientSecret(),
                    'scope' => 'cob.write cob.read pix.write pix.read',
                    // 'scope' => $scope, //'cob.write, cob.read', 
                    'grant_type' => 'client_credentials'
                ))
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

            // Receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $server_response = curl_exec($ch);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error !== '') {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(token) Erro na API...\n");
                $this->detalhe->setReturnAPI($error);
                return;
            }

            if ($server_response == '') {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(token) Resposta vazia no procemento do token, provavelmente o limite de chamadas foi atingido...\n");
                return;
            }

            $this->detalhe->setReturnAPI($server_response);
            $arrayJson = json_decode($server_response, true);

            if ((!isset($arrayJson['access_token']) or empty($arrayJson['access_token']))) {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(token) access_token não encontrado...\n");
                return;
            }

            $agora = new \DateTime(); // hora atual
            $agora->add(new \DateInterval('PT45M')); // soma 45 minutos
            $this->autenticacao->setAccessToken($arrayJson['access_token']);
            $this->autenticacao->setTokenExpiraEm($agora);
        } catch (\Exception $e) {
            $this->detalhe->setStatus('exception');
            $this->detalhe->setReturn("(token) exception: " . $e->getMessage() . " linha: " . $e->getLine() . " ...\n");
        }
    }

    public function gerar(): void
    {
        try {

            if ($this->validarToken() == false) {
                $this->token();
            }

            $auth = 'Authorization: Bearer ' . $this->autenticacao->getAccessToken();
            $cc = 'x-conta-corrente: ' . $this->recebedor->getConta();
            $json = 'Content-Type: application/json';

            $devedor = '';
            if ($this->devedor->getCpfCnpj() != "") {
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
            if (($this->detalhe->getInfoAdicionais() > 0) and (!empty($this->detalhe->getInfoAdicionais())) and ($this->detalhe->getInfoAdicionais() != null)) {
                $infoAdicionais = '"infoAdicionais":' . json_encode($this->detalhe->getInfoAdicionais());
            }

            $data = <<<DATA
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
            curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/pix/v2/cob/" . $this->detalhe->getTxid());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, $cc, $json));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, $json));
            curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
            curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $jsonResult = curl_exec($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);

            curl_close($ch);

            if ($error !== '') {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(gerar) Erro na API...\n");
                $this->detalhe->setReturnAPI($error);
                return;
            }

            if (Validate::json($jsonResult) == '') {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(gerar) Resposta não é um JSON...\n");
                $this->detalhe->setReturnAPI($jsonResult);
                return;
            }

            $arrayResult = json_decode($jsonResult, true);

            $status = Convert::arrayToValue($arrayResult, 'status');

            if ((!isset($status) or empty($status))) {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(gerar) status não encontrado!...\n");
                return;
            }

            $pixCopiaECola = Convert::arrayToValue($arrayResult, 'pixCopiaECola');

            if ((!isset($pixCopiaECola) or empty($pixCopiaECola))) {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(gerar) pixCopiaECola não encontrado!...\n");
                return;
            }

            $this->detalhe->setStatus($status);
            $this->detalhe->setPixCopiaECola($pixCopiaECola);
        } catch (\Exception $e) {
            $this->detalhe->setStatus('exception');
            $this->detalhe->setReturn("(gerar) exception: " . $e->getMessage() . " linha: " . $e->getLine() . " ...\n");
        }
    }

    public function consultar(): void
    {
        try {
            if ($this->validarToken() == false) {
                $this->token();
            }
            $auth = 'Authorization: Bearer ' . $this->autenticacao->getAccessToken();
            $cc = 'x-conta-corrente: ' . $this->recebedor->getConta();
            $json = 'Content-Type: application/json';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/pix/v2/cob/" . $this->detalhe->getTxid());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, $cc, $json));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, $json));
            curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
            curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $jsonResult = curl_exec($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);

            curl_close($ch);

            if ($error !== '') {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(Consultar) Erro na API...\n");
                $this->detalhe->setReturnAPI($error);
                return;
            }

            $this->detalhe->setReturnAPI($jsonResult);

            if (Validate::json($jsonResult) == '') {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(consultar) Resposta não é um JSON...\n");
                $this->detalhe->setReturnAPI($jsonResult);
                return;
            }

            $arrayResult = json_decode($jsonResult, true);

            if ((isset($arrayResult['loc']['id']) or !empty($arrayResult['loc']['id']))) {
                $this->detalhe->setId($arrayResult['loc']['id']);
            }

            if ((isset($arrayResult['txid']) or !empty($arrayResult['txid']))) {
                $this->detalhe->setStatus($arrayResult['txid']);
            }

            if ((isset($arrayResult['pix'][0]['endToEndId']) or !empty($arrayResult['pix'][0]['endToEndId']))) {
                $this->detalhe->setEndToEndId($arrayResult['pix'][0]['endToEndId']);
            }

            if ((isset($arrayResult['status']) or !empty($arrayResult['status']))) {
                $this->detalhe->setStatus($arrayResult['status']);
            }

            if ((isset($arrayResult['pixCopiaECola']) or !empty($arrayResult['pixCopiaECola']))) {
                $this->detalhe->setPixCopiaECola($arrayResult['pixCopiaECola']);
            }

            if ((isset($arrayResult['valor']['original']) or !empty($arrayResult['valor']['original']))) {
                $this->detalhe->setValor($arrayResult['valor']['original']);
            }

            if ((isset($arrayResult['pix'][0]['valor']) or !empty($arrayResult['pix'][0]['valor']))) {
                $this->detalhe->setValorPago($arrayResult['pix'][0]['valor']);
            }

            // carrega as devoluções
            $this->detalhe->devolucoes = Convert::arrayToValue($arrayResult, 'devolucoes');
            $valorDevolucao = 0;
            foreach ($this->detalhe->devolucoes as $devolucao) {
                $valorDevolucao += Convert::strToFloat($devolucao['valor']);
            }

            $this->detalhe->setValorDevolucao($valorDevolucao);
            $this->detalhe->infoAdicionais = Convert::arrayToValue($arrayResult, 'infoAdicionais');
            $criacao = Convert::arrayToValue($arrayResult, 'criacao');
            $expiracao = Convert::arrayToValue($arrayResult, 'expiracao');

            if ((!isset($criacao) or empty($criacao))) {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(consultar) Pix sem data de criação...\n");
                $this->detalhe->setReturnAPI($jsonResult);
                return;                
            }

            if ((!isset($expiracao) or empty($expiracao))) {
                $this->detalhe->setStatus('error');
                $this->detalhe->setReturn("(consultar) Pix sem data de expiração...\n");
                $this->detalhe->setReturnAPI($jsonResult);
                return;                
            }

            if (mb_strtoupper($this->detalhe->getStatus()) <> 'CONCLUIDA') {
                if (Validate::pix($criacao, $expiracao) == false) {
                    $this->detalhe->setStatus('expirado');
                }
            }
        } catch (\Exception $e) {
            $this->detalhe->setStatus('exception');
            $this->detalhe->setReturn("(Concultar) exception: " . $e->getMessage() . " linha: " . $e->getLine() . " ...\n");
        }
    }

    public function pagar(): void
    {
        if ($this->validarToken() == false) {
            $this->token();
        }
        $auth = 'Authorization: Bearer ' . $this->autenticacao->getAccessToken();
        $cc = 'x-conta-corrente: ' . $this->recebedor->getConta();
        $json = 'Content-Type: application/json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/pix/v2/cob/" . $this->detalhe->getTxid());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, $cc, $json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth, $json));
        curl_setopt($ch, CURLOPT_SSLCERT, $this->autenticacao->getCertificado());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->autenticacao->getChave());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close($ch);

        if ($error !== '') {
            throw new \Exception($error);
        }

        $jsonResult = json_decode($result);
        $this->detalhe->setStatus($jsonResult->status);
        $this->detalhe->setPixCopiaECola($jsonResult->pixCopiaECola);
    }
}
