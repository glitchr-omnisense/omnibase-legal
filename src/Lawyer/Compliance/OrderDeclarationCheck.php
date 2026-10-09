<?php

namespace Base\Legal\Lawyer\Compliance;

use Base\Legal\Practice\Compliance\DeclarationCheck;

/**
 * « L'avocat qui ouvre ou modifie substantiellement un site Internet doit
 * en informer le conseil de l'Ordre sans délai et lui communiquer les noms
 * de domaine qui permettent d'y accéder » (RIN art. 10.5). The settings
 * keep which bar and the day the Ordre was told; the back office's memo
 * lists what to send.
 */
final class OrderDeclarationCheck extends DeclarationCheck
{
    protected function getLabel(): string
    {
        return 'lawyer.compliance.order';
    }
}
