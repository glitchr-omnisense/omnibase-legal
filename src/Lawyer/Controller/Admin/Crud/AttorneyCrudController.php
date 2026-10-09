<?php

namespace Base\Legal\Lawyer\Controller\Admin\Crud;

use Base\Admin\Config\Actions;
use Base\Admin\Config\Crud;
use Base\Admin\Controller\AbstractCrudController;
use Base\Field\AssociationField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\SelectField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Base\Legal\Lawyer\Entity\Attorney;
use Base\Legal\Lawyer\Enum\Specialisation;
use Base\Legal\Practice\Controller\Admin\AdminScreenTrait;

/**
 * A member of the team as a lawyer: bar, oath, and what RIN art. 10.2
 * frames - the certified specialisations, picked in the official list (two
 * at most), and the dominant fields (three at most).
 */
class AttorneyCrudController extends AbstractCrudController
{
    use AdminScreenTrait;

    public static function getEntityFqcn(): string
    {
        return Attorney::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-scale-balanced';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('@legal.lawyer.admin.attorney.singular')
            ->setEntityLabelInPlural('@legal.lawyer.admin.attorney.plural');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $this->openTo(parent::configureActions($actions), 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('member', '@legal.lawyer.admin.field.member')->setColumns(6);
        yield TextField::new('bar', '@legal.lawyer.admin.field.bar')->setColumns(4)->setRequired(false)->setHelp('@legal.lawyer.admin.help.bar');
        yield IntegerField::new('swornIn', '@legal.lawyer.admin.field.sworn_in')->setColumns(2)->setRequired(false);
        yield SelectField::new('specialisationValues', '@legal.lawyer.admin.field.specialisations')->setChoices(Specialisation::choices())->allowMultipleChoices()->setRequired(false)->hideOnIndex()->setHelp('@legal.lawyer.admin.help.specialisations');
        yield TextField::new('qualification', '@legal.lawyer.admin.field.qualification')->setRequired(false)->hideOnIndex()->setHelp('@legal.lawyer.admin.help.qualification');
        yield TextareaField::new('dominantFieldsText', '@legal.lawyer.admin.field.dominant_fields')->setRequired(false)->hideOnIndex()->setHelp('@legal.lawyer.admin.help.dominant_fields');
    }
}
