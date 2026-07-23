<?php

namespace App\Enum;

enum SeasonStatusEnum: string
{
    case DRAFT = 'Saison à venir';         
    case ACTIVE = 'Saison en cours';       
    case ARCHIVED = 'Saison archivée';  

    // Méthode optionnelle pour afficher des libellés lisibles dans l'UI
    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'En préparation',
            self::ACTIVE => 'En cours',
            self::ARCHIVED => 'Archivée',
        };
    }

    // Couleurs pour vos badges Tailwind CSS (pratique dans Twig)
    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-amber-50 text-amber-700 border-amber-200',
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::ARCHIVED => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}