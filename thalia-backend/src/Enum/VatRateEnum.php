<?php

namespace App\Enum;

enum VatRateEnum: string
{
    case DEFAULT_VAT = '20.00';
    case SHOW_VAT = '5.50';
   
    public function getLabel(): string
    {
        return match($this) {
            self::DEFAULT_VAT => 'Taux standard (20 %)',
            self::SHOW_VAT => 'Taux réduit spectacle (5,5 %)',
           
        };
    }

    // calcul de la valeur numérqiue
    public function getMultiplier(): float
    {
        return ((float) $this->value) / 100;
    }
}