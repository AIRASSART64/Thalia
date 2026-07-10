<?php

namespace App\Enum;

enum ErpType: string
{
    case TYPE_L = 'Type L (Salles d\'auditions, de conférences, de spectacles)';
    case TYPE_M = 'Type M (Magasins, Centres commerciaux)';
    case TYPE_N = 'Type N (Restaurants, Débits de boissons)';
    case TYPE_X = 'Type X (Établissements sportifs couverts)';
    case TYPE_Y = 'Type Y (Musées, Monuments historiques)';
   
}