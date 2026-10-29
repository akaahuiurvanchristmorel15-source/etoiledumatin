<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Paramètre d'URL lu comme texte simple, borné en longueur.
     * Un tableau (« ?search[]=… ») ou toute autre forme inattendue est ignoré au lieu de provoquer une erreur.
     */
    protected function queryText(Request $request, string $key, int $maxLength = 100): string
    {
        $value = $request->query($key);

        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }
}
