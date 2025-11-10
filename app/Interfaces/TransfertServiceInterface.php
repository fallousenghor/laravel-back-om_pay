<?php

namespace App\Interfaces;

interface TransfertServiceInterface
{
    public function verifierDestinataire($numeroTelephone);
    public function initierTransfert($utilisateur, $data);
    public function confirmerTransfert($utilisateur, $idTransfert, $codePin);
    public function annulerTransfert($utilisateur, $idTransfert);
}