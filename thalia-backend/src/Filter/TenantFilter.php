<?php

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Si l'entité demandée n'a pas de lien avec une organisation, on n'applique pas de restriction
        if (!$targetEntity->hasAssociation('organization')) {
            return '';
        }

        // Injecte automatiquement "WHERE organization_id = X" à chaque requête SQL générée par Doctrine
        return sprintf('%s.organization_id = %s', $targetTableAlias, $this->getParameter('current_tenant_id'));
    }
}
