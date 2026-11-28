<?php

return [
    'too_many_requests' => 'Trop de requêtes effectuées pour l\'adresse IP actuelle. Voir les en-têtes de réponse pour plus d\'informations.',
    'invalid_origin' => 'L\'accès au service est refusé car le domaine d\'origine de la requête n\'est pas autorisé selon la politique de sécurité.',
    'invalid_api_key' => 'L\'accès au service est refusée car une clé API est requise et n\'a pas été fournie ou est invalide. Veuillez fournir une clé API valide dans l\'en-tête HTTP « X-Api-Key ».',
    'internal_server_error' => 'Une erreur est survenue lors du traitement de votre requête.',
    'not_found' => 'La ressource demandée est introuvable.',
    'unexpected_authorization' => 'Cette route ne lit pas l\'en-tête HTTP « Authorization ». Veuillez le retirer.'
];
