<?php

namespace App\Enum;

enum FinancialCategoryEnum: string
{
    // Dépenses (DÉBIT)
    case MASSE_SALARIALE = 'masse_salariale';
    case PLATEAU = 'plateau';
    case HEBERGEMENT_RESTAURATION_TRANSPORT = 'hebergement_restauration_transport';
    case DROITS = 'droits';
    case CONSOMMABLES_MAINTENANCE = 'consommables_maintenance';
    case COMMUNICATION_DIFFUSION = 'communication_diffusion';
    case ADMINISTRATIFS_DIVERS = 'administratifs_divers';

    // Recettes (CRÉDIT)
    case BILLETTERIE = 'billetterie';
    case COPRUDUCTION = 'coproduction';
    case SUBVENTIONS = 'subventions';
    case AIDES = 'aides';
    case MECENAT_PARRAINAGE = 'mecenat_parrainage';

    public function getFinancialTypeEnum(): FinancialTypeEnum
    {
        return match($this) {
            self::MASSE_SALARIALE,
            self::PLATEAU,
            self::HEBERGEMENT_RESTAURATION_TRANSPORT,
            self::DROITS,
            self::CONSOMMABLES_MAINTENANCE,
            self::COMMUNICATION_DIFFUSION,
            self::ADMINISTRATIFS_DIVERS => FinancialTypeEnum::DEBIT,

            self::BILLETTERIE,
            self::COPRUDUCTION,
            self::SUBVENTIONS,
            self::AIDES,
            self::MECENAT_PARRAINAGE => FinancialTypeEnum::CREDIT,
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::MASSE_SALARIALE => "Salaires, indemnités, charges sociales, notes de frais",
            self::PLATEAU => "Achat et location matériel pour décors, accessoires et costumes",
            self::HEBERGEMENT_RESTAURATION_TRANSPORT => "Frais d'hébergement, de restauration et de transport",
            self::DROITS => "Droits d'auteur, droits annexes",
            self::CONSOMMABLES_MAINTENANCE => "Consommables et frais de maintenance",
            self::COMMUNICATION_DIFFUSION => "Frais de communication et de diffusion",
            self::ADMINISTRATIFS_DIVERS => "Frais administratifs et divers",
            
            self::BILLETTERIE => "Recette de la billetterie",
            self::COPRUDUCTION => "Financements et cofinancements par les partenaires",
            self::SUBVENTIONS => "Subventions publiques",
            self::AIDES => "Aides des sociétés civiles",
            self::MECENAT_PARRAINAGE => "Financements issus du mécénat et du parrainage",
        };
    }
}