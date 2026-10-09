<?php

namespace Base\Legal\Practice\Controller\Admin\Crud;

use Base\Admin\Config\Actions;
use Base\Admin\Config\Crud;
use Base\Admin\Controller\AbstractCrudController;
use Base\Field\AssociationField;
use Base\Field\BooleanField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\SlugField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Base\Legal\Practice\Entity\Area;
use Base\Legal\Practice\ProfileInterface;
use Base\Office\Controller\Admin\OpenToTrait;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * The fields of practice as the site shows them. What is done in a field is
 * named by the profession (a notary's "Actes et démarches", a lawyer's
 * "Questions traitées"), and so is what its text must keep from.
 */
class AreaCrudController extends AbstractCrudController
{
    use OpenToTrait;

    private string $profession = 'lawyer';

    #[Required]
    public function setLegalProfile(ProfileInterface $profile): void
    {
        $this->profession = $profile->getProfession()->value;
    }

    public static function getEntityFqcn(): string
    {
        return Area::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-folder-tree';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('@legal.admin.area.singular')
            ->setEntityLabelInPlural('@legal.admin.area.plural')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $this->openTo(parent::configureActions($actions), 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $own = '@legal.'.$this->profession.'.admin.';

        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name', '@legal.admin.field.name')->setColumns(6);
        yield SlugField::new('slug')->setColumns(4)->hideOnIndex();
        yield IntegerField::new('position', '@legal.admin.field.position')->setColumns(2)->hideOnIndex();
        yield TextareaField::new('summary', '@legal.admin.field.summary')->setRequired(false)->hideOnIndex();
        yield TextareaField::new('body', '@legal.admin.field.body')->setRequired(false)->hideOnIndex()->setHelp($own.'help.body');
        yield TextareaField::new('itemsText', $own.'field.items')->setRequired(false)->hideOnIndex()->setHelp($own.'help.items');
        yield AssociationField::new('members', '@legal.admin.field.members')->setColumns(8)->setRequired(false)->hideOnIndex();
        yield BooleanField::new('active', '@legal.admin.field.active')->setColumns(2);
    }
}
