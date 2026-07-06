<?php

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Si l'entité n'a pas de lien avec une organisation, on n'applique pas de filtre
        if (!$targetEntity->hasAssociation('organization')) {
            return '';
        }

        //Si le paramètre 'current_tenant_id' n'a pas encore été injecté 
        // (par exemple lors de la phase de connexion ou pour un SuperAdmin), on n'applique PAS le filtre 
        // plutôt que de faire crasher toute l'application.
        try {
            $tenantId = $this->getParameter('current_tenant_id');
        } catch (\InvalidArgumentException $e) {
            // Le paramètre n'existe pas encore au moment de la requête : on ignore le filtre
            return '';
        }

        //Si le paramètre existe, on applique le filtre normalement
        return sprintf('%s.organization_id = %s', $targetTableAlias, $tenantId);
    }
}
