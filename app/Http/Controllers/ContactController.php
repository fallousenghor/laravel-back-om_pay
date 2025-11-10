<?php

namespace App\Http\Controllers;

use App\Interfaces\ContactServiceInterface;
use App\Http\Requests\ListerContactsRequest;
use App\Http\Requests\AjouterContactRequest;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactServiceInterface $contactService)
    {
        $this->contactService = $contactService;
    }

    // 5.1 Lister les Contacts
    public function listerContacts(ListerContactsRequest $request)
    {
        $utilisateur = $request->user();
        $page = $request->get('page', 1);
        $limite = $request->get('limite', 50);

        $filters = $request->only(['recherche']);

        $result = $this->contactService->listerContacts($utilisateur, $filters, $page, $limite);
        return $this->responseFromResult($result);
    }

    // 5.2 Ajouter un Contact
    public function ajouterContact(AjouterContactRequest $request)
    {
        $utilisateur = $request->user();
        $result = $this->contactService->ajouterContact($utilisateur, $request->validated());
        return $this->responseFromResult($result);
    }
}
