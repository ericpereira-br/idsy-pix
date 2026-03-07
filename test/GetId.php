<?php

namespace idsy\pix;

require '../vendor/autoload.php';

use Idsy\Tools\Create;

$sequencia = 1;
$txid = Create::txid('TEST', $sequencia);
echo $txid;
