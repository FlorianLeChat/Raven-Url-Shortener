<?php

return [
    'report.accepted.subject' => 'Your report has been accepted',
    'report.accepted.body' => "Hello,\n\n"
        . "Thank you for reporting the shortcut link \"%slug%\". "
        . "An administrator has reviewed your report and disabled the link.\n\n"
        . 'Raven Url Shortener',
    'report.rejected.subject' => 'Your report has been reviewed',
    'report.rejected.body' => "Hello,\n\n"
        . "Thank you for reporting the shortcut link \"%slug%\". "
        . "An administrator has reviewed your report and found no reason to disable the link: it remains available.\n\n"
        . 'Raven Url Shortener'
];
