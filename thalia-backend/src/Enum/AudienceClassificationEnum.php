<?php

namespace App\Enum;

enum AudienceClassificationEnum: string
{
    case Tout_public = 'Tout public';
    case Public_informe = 'Public averti';
    case public_enfant  = 'Public à partir de 6 ans';
   
}