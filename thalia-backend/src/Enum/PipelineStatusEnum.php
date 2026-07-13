<?php

namespace App\Enum;

enum PipelineStatusEnum : string
{
    case CREATION = 'Creéation';
    case A_CONTACTER = 'A contacter';
    case PRISE_CONTACT = 'Prise de contact';
    case NEGOCIATION = 'Négociation';
    case ATTENTE = 'Attente';
    case VALIDE = 'Validé';
    case CONTRAT_TRANSMIS = 'Contrat transmis pour signature';
    case CONTRAT_RETOUR = 'Contrat retourné signé';

}