<?php

namespace Base\Legal\Notary\Compliance;

use Base\Legal\Practice\Compliance\DeclarationCheck;

/**
 * "L'office notarial qui ouvre un site internet, y propose ses services ou
 * ouvre ou modifie une ou plusieurs pages web destinées aux mêmes fins [...]
 * doit en informer sans délai la chambre des notaires dont il dépend"
 * (règlement professionnel du notariat, art. 14.4). The settings keep which
 * chamber and the day it was told.
 */
final class ChamberDeclarationCheck extends DeclarationCheck
{
    protected function getLabel(): string
    {
        return 'notary.compliance.chamber';
    }
}
