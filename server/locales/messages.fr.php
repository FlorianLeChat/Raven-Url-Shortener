<?php

return [
	'http.too_many_requests' => 'Trop de requêtes effectuées pour l\'adresse IP actuelle. Voir les en-têtes de réponse pour plus d\'informations.',
	'http.invalid_origin' => 'L\'accès au service est refusé car le domaine d\'origine de la requête n\'est pas autorisé selon la politique de sécurité.',
	'link.unreachable_url' => 'L\'URL spécifiée est inaccessible.',
	'link.disabled' => 'Le lien raccourci spécifié a été désactivé par son propriétaire ou par un administrateur.',
	'link.reported' => 'Le lien raccourci spécifié a été signalé par un ou plusieurs utilisateurs. Ce lien doit être vérifié par un administrateur pour être de nouveau accessible.',
	'link.password.missing' => 'Le lien raccourci spécifié est protégé par un mot de passe. Veuillez fournir le fournir dans l\'en-tête HTTP « Authorization ».',
	'link.password.invalid' => 'Le mot de passe que vous avez fourni pour le lien raccourci est invalide. Veuillez le vérifier et réessayer.',
	'report.duplicated' => 'Vous avez déjà signalé ce lien raccourci, vous ne pouvez pas le signaler à nouveau.',
	'report.trusted_link' => 'Vous ne pouvez pas signaler un lien de confiance. Si vous pensez que ce lien est malveillant, veuillez contacter un administrateur.',
	'report.maximum_reached' => 'Le nombre maximum de signalements pour ce lien a été atteint (%max%). Il ne peut pas être signalé à nouveau.',
	'slug.already_used' => 'Le slug personnalisé que vous avez choisi est déjà utilisé par un autre lien. Veuillez en choisir un autre.',
	'api_key.missing' => 'La clé API est manquante. Veuillez la fournir dans l\'en-tête HTTP « Authorization ».',
	'api_key.invalid' => 'La clé API fournie est invalide. Veuillez la vérifier et réessayer.'
];