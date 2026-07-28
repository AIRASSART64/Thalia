<?php

namespace App\Service;

use App\Entity\Organization;
use App\Entity\Theme;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ThemeTransformerService implements DataTransformerInterface
{
    private ?Organization $organization = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * Transforme la Collection d'objets Theme en CHAÎNE séparée par des virgules
     * (Évite le PHP Warning "Array to string conversion" dans Twig)
     */
    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        // Si c'est une collection Doctrine (depuis l'entité Show)
        if ($value instanceof Collection) {
            $ids = array_map(fn(Theme $theme) => $theme->getId(), $value->toArray());
            return implode(',', $ids);
        }

        // Si c'est déjà un tableau
        if (is_array($value)) {
            $ids = array_map(fn($item) => $item instanceof Theme ? $item->getId() : $item, $value);
            return implode(',', $ids);
        }

        return (string) $value;
    }

    /**
     * Transforme la chaîne de caractères (ou tableau) soumise par TomSelect
     * en une Collection d'entités Theme pour l'entité Show.
     */
    public function reverseTransform(mixed $value): Collection
    {
        if (empty($value)) {
            return new ArrayCollection();
        }

        // Convertit la chaîne "1,4,Nouveau" en tableau ["1", "4", "Nouveau"]
        if (is_string($value)) {
            $value = array_filter(array_map('trim', explode(',', $value)));
        }

        if (!is_array($value)) {
            return new ArrayCollection();
        }

        $themes = new ArrayCollection();
        $repo = $this->entityManager->getRepository(Theme::class);

        foreach ($value as $item) {
            $cleanValue = trim((string) $item);
            if ('' === $cleanValue) {
                continue;
            }

            $themeFound = null;

            // 1. Recherche par ID si c'est un entier/numérique
            if (is_numeric($cleanValue)) {
                $themeFound = $repo->find((int) $cleanValue);
            }

            // 2. Recherche par Nom si introuvable par ID
            if (!$themeFound) {
                $criteria = ['name' => $cleanValue];
                if ($this->organization) {
                    $criteria['organization'] = $this->organization;
                }
                $themeFound = $repo->findOneBy($criteria);
            }

            // 3. Création à la volée du nouveau thème s'il n'existe pas encore
            if (!$themeFound) {
                $themeFound = new Theme();
                $themeFound->setName($cleanValue);
                if ($this->organization) {
                    $themeFound->setOrganization($this->organization);
                }

                $this->entityManager->persist($themeFound);
            }

            if (!$themes->contains($themeFound)) {
                $themes->add($themeFound);
            }
        }

        return $themes;
    }
}