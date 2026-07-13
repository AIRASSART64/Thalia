<?php

namespace App\Enum;


enum DisciplineEnum : string
{
    case THEATRE = 'Théatre : classique, contemorain, humour';
    case DANSE = 'Danse : spectacle de danse et ballets';
    case RUE = ' Rue : spzctacle et arts de la rue ';
    case CIRQUE = 'Cirque : arts du cirque';
    case OPERA = 'Opéra ';
    case MSIQUE_LIVE = ' Musique Live : tout type de musique';
    case MARIONNETTE = 'Marionnette : arts de la marionnette';
    case ILLUSIONISME = "Illusionnisme: spectacle d'illusionnisme et de magie";
    case POESIE = 'Poésie';

}
