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
     * Transforme la Collection d'objets Theme en CHAÎNE de NOMS séparés par des virgules
     * pour l'affichage initial dans le formulaire.
     */
    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        // Si c'est une collection Doctrine (depuis l'entité Show)
        if ($value instanceof Collection) {
            // ⚠️ CORRECTION : On extrait getName() et non getId()
            $names = array_map(fn(Theme $theme) => $theme->getName(), $value->toArray());
            return implode(',', $names);
        }

        // Si c'est déjà un tableau
        if (is_array($value)) {
            // ⚠️ CORRECTION : On extrait getName() si c'est un objet Theme
            $names = array_map(fn($item) => $item instanceof Theme ? $item->getName() : $item, $value);
            return implode(',', $names);
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

        // Convertit la chaîne "Théâtre, Mime, Nouveau" en tableau ["Théâtre", "Mime", "Nouveau"]
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

            // 1. Recherche directe par Nom
            $criteria = ['name' => $cleanValue];
            if ($this->organization) {
                $criteria['organization'] = $this->organization;
            }
            $themeFound = $repo->findOneBy($criteria);

            // 2. Recherche par ID (au cas où TomSelect enverrait un ID numérique)
            if (!$themeFound && is_numeric($cleanValue)) {
                $themeFound = $repo->find((int) $cleanValue);
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