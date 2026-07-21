<?php

namespace App\Enum;

enum FinancialCategoryEnum: string
{
    case MASSE_SALARIALE = 'Salaires, indéminités, charges sociales, notes de frais';
    case PLATEAU = 'Achat et location materiel pour décors, accessoires et costumes ';
    case HEBERGEMENT_RESTAURATION_TRANSPORT = "Frais d'hébérgement, de restauration et de transport";
    case DROITS = "Droits d'auteur, droits annexes ";
    case CONSOMMABLES_MAINTENANCE = "Consommables et frais de maintenance";
    case COMMUNICATION_DIFFUSION = "Frais de communication et de diffusion";
    case ADMINISTRATIFS_DIVERS = "Frais adminitratifs et divers";
    case BILLETTERIE = "Recette de la billeterie";
    case COPRUDUCTION = "Financements et cofinancements par les partenaires";
    case SUBVENTIONS = "Subventions publiques";
    case AIDES = "Aides des socités civiles";
    case MECENAT_PARRAINAGE = "Financements issus du mécénat et du parrainage";

}
