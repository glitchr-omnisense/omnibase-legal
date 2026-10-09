<?php

namespace Base\Legal\Practice\Compliance;

use Base\Legal\Practice\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * The site is declared to the professional body: the settings keep which
 * one (the bar, the chamber) and the day it was told. Which rule asks it,
 * and in which words, is the profession's - its profile extends this check
 * and names its label (Base\Legal\Lawyer\Compliance\OrderDeclarationCheck,
 * Base\Legal\Notary\Compliance\ChamberDeclarationCheck).
 *
 * Under the label "<label>": "<label>_body_advice" when the body is not
 * named, "<label>_advice" (with {body}) when no date is typed,
 * "<label>_future" when the date is yet to come.
 */
abstract class DeclarationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record)
    {
    }

    /** The check's label, a key of the `legal` domain: "notary.compliance.chamber". */
    abstract protected function getLabel(): string;

    public function check(): ComplianceResult
    {
        $label = $this->getLabel();
        $body = $this->record->getBody();
        if (null === $body) {
            return new ComplianceResult($label, ComplianceResult::MISSING, $label.'_body_advice', 'legal');
        }
        $declared = $this->record->getDeclaredAt();
        if (null === $declared) {
            return new ComplianceResult($label, ComplianceResult::MISSING, $label.'_advice', 'legal', ['body' => $body]);
        }
        if ($declared > new \DateTimeImmutable('tomorrow')) {
            return new ComplianceResult($label, ComplianceResult::WARNING, $label.'_future', 'legal');
        }

        return ComplianceResult::ok($label, 'legal');
    }
}
