Idsy Tools

Biblioteca utilitária para PHP contendo helpers para:

🚀 Recursos

Convert – Manipulação e conversão de valores

Create – Geração de logs e utilidades

Email – Envio de e-mails via SMTP

Validate – Validação de CPF, CNPJ e outros dados

📦 Requisitos

PHP >= 8.1

Composer

📦 Instalação

Via Composer:

composer require idsy/tools

🔧 Como usar
🔹 Convert (Métodos estáticos)
use Idsy\Tools\Convert;

$valor = "CPF: 123.456.789-00";

$resultado = Convert::onlyNumber($valor);

echo $resultado;
// Saída: 12345678900

🔹 Email (Instância)
use Idsy\Tools\Email;

$mail = new Email();

// Configuração SMTP
$mail->host     = 'smtp.seudominio.com';
$mail->username = 'usuario';
$mail->password = 'senha';
$mail->port     = 587;
$mail->secure   = 'tls';

// Remetente e destinatário
$mail->from     = 'remetente@dominio.com';
$mail->to       = 'destinatario@dominio.com';
$mail->subject  = 'Título do e-mail';
$mail->message  = 'Corpo do e-mail';

$mail->send();
📁 Estrutura do Projeto
src/
 ├── Convert.php
 ├── Create.php
 ├── Email.php
 └── Validate.php