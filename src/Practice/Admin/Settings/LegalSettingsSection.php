<?php

namespace Base\Legal\Practice\Admin\Settings;

use Base\Admin\Settings\SettingsSectionInterface;
use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Practice\Service\Record;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The settings' section of the practice: what the legal notice and the
 * page of the fees print and only the practice knows. Which fields, and
 * under which words, is the profession's (its record's getFields(), the
 * labels <profession>.settings.* of the `legal` domain) - a firm's
 * denomination, structure, bar and network; an office's holder, CRPCEN
 * number, chamber, discounts. The compliance widget reads the same.
 */
#[AsTaggedItem(priority: 40)]
final class LegalSettingsSection implements SettingsSectionInterface
{
    public function __construct(
        private readonly ProfileInterface $profile,
        private readonly Record $record,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getPage(): string
    {
        return self::SETTINGS;
    }

    public function getFields(): array
    {
        $long = $this->record->getLongFields();
        $fields = [];
        foreach ($this->record->getFields() as $key => $name) {
            $fields[$key] = ['required' => false, 'label' => $this->translator->trans($this->profile->getProfession()->value.'.settings.'.$name, [], 'legal')]
                + (\in_array($key, $long, true) ? ['form_type' => TextareaType::class] : []);
        }

        return $fields;
    }
}
