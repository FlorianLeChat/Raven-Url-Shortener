<?php

return [
    'report.accepted.subject' => 'Votre signalement a été accepté',
    'report.accepted.body' => "Bonjour,\n\n"
        . "Merci d'avoir signalé le lien raccourci « %slug% ». "
        . "Un administrateur a examiné votre signalement et a désactivé le lien.\n\n"
        . 'Raven Url Shortener',
    'report.rejected.subject' => 'Votre signalement a été examiné',
    'report.rejected.body' => "Bonjour,\n\n"
        . "Merci d'avoir signalé le lien raccourci « %slug% ». "
        . "Un administrateur a examiné votre signalement et n'a trouvé aucune raison de désactiver le lien : "
        . "il reste accessible.\n\n"
        . 'Raven Url Shortener'
];
