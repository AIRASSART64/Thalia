<?php

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Si l'entité n'a pas de lien avec une organisation, aucun filtre n'est activé
        if (!$targetEntity->hasAssociation('organization')) {
            return '';
        }

        //Si le paramètre 'current_tenant_id' n'a pas encore été injecté 
        try {
            $tenantId = $this->getParameter('current_tenant_id');
        } catch (\InvalidArgumentException $e) {
            // le filtre ignoré
            return '';
        }

        //Si le paramètre existe, on applique le filtre normalement
        return sprintf('%s.organization_id = %s', $targetTableAlias, $tenantId);
    }
}
