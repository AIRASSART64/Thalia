<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchApiService
{
    private const API_URL = 'https://recherche-entreprises.api.gouv.fr/search';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     *
     * @return array{name: string, business_name: string, siret: string, siren: string, statut_licence: ?string}|null
     *
     * @throws \InvalidArgumentException si le SIRET est syntaxiquement invalide
     * @throws \Exception si l'API est indisponible ou répond en erreur
     */
    public function verifyEntrepreneurSpectacle(string $siret): ?array
    {
        $siret = preg_replace('/\s+/', '', $siret);
        $this->assertValidSiret($siret);

        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => ['q' => $siret],
                'timeout' => 4.0,
                'headers' => ['User-Agent' => 'Thalia/1.0'],
            ]);

            $data = $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('API Recherche Entreprises inaccessible', ['siret' => $siret,'exception' => $e->getMessage(),
            ]);
            throw new \Exception('API de vérification inaccessible.', previous: $e);
        } catch (ClientExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface $e) {
            $this->logger->error('API Recherche Entreprises a répondu en erreur', ['siret' => $siret]);
            throw new \Exception('API de vérification a répondu en erreur.', previous: $e);
        } catch (DecodingExceptionInterface $e) {throw new \Exception('Réponse API de vérification illisible.', previous: $e);
        }

        if (empty($data['results'])) {
            $this->logger->info('SIRET non trouvé dans la base nationale', ['siret' => $siret]);
            return null;
        }

        $company = $data['results'][0];

        // Vérification que l'entreprise soit active
        if (($company['etat_administratif'] ?? 'A') !== 'A') {
            $this->logger->info('Entreprise trouvée mais fermée', ['siret' => $siret]);
            return null;
        }

        $complements = $company['complements'] ?? [];
        $isEntrepreneur = $complements['est_entrepreneur_spectacle'] ?? false;

        if (!$isEntrepreneur) {
            $this->logger->info('Entreprise non reconnue comme entrepreneur de spectacles', ['siret' => $siret]);
            return null;
        }

        $name = $company['nom_complet'] ?? $company['nom_raison_sociale'] ?? 'Structure inconnue';
        $businessName = $company['nom_raison_sociale'] ?? $name;

        return [
            'name' => $name,
            'business_name' => $businessName,
            'siret' => $siret,
            'siren' => substr($siret, 0, 9),
            'statut_licence' => $complements['statut_entrepreneur_spectacle'] ?? null,
        ];
    }

    private function assertValidSiret(string $siret): void
    {
        if (!preg_match('/^\d{14}$/', $siret)) {
            throw new \InvalidArgumentException(sprintf('Le SIRET "%s" est invalide (14 chiffres attendus).', $siret));
        }
    }
}