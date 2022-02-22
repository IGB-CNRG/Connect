<?php

namespace App\Controller\Admin;

use App\Entity\KeyAffiliation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class KeyAffiliationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return KeyAffiliation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('person'),
            AssociationField::new('cylinderKey'),
        ];
    }
}
