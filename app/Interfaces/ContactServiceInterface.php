<?php

namespace App\Interfaces;

interface ContactServiceInterface
{
    public function listerContacts($utilisateur, $filters, $page, $limite);
    public function ajouterContact($utilisateur, $data);
}