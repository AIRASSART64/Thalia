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
    // API unique et officielle du gouvernement français (Gratuite, haut débit, sans redirection)
    private const API_URL = 'https://recherche-entreprises.api.gouv.fr/search';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception si l'API est inaccessible ou renvoie une erreur
     * @throws \InvalidArgumentException si le format du SIRET est invalide
     */
    public function fetchOrganizationBySiret(string $siret): ?array
    {
        $this->assertValidSiret($siret);

        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => [
                    'q' => $siret,
                    'limite_matching_etablissements' => 1,
                ],
                'timeout' => 4.0,
            ]);

            $data = $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('API Recherche Entreprises inaccessible (transport)', [
                'siret' => $siret,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception('API de vérification inaccessible.', previous: $e);
        } catch (ClientExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface $e) {
            $this->logger->error('API Recherche Entreprises a répondu en erreur', [
                'siret' => $siret,
                'status_code' => $e->getResponse()->getStatusCode(),
            ]);
            throw new \Exception('API de vérification a répondu en erreur.', previous: $e);
        } catch (DecodingExceptionInterface $e) {
            $this->logger->error('Réponse API Recherche Entreprises illisible', [
                'siret' => $siret,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception('Réponse API de vérification illisible.', previous: $e);
        }

        // 1. SIRET non trouvé dans la base SIRENE
        if (empty($data['results'])) {
            $this->logger->info('SIRET non trouvé dans la base nationale', ['siret' => $siret]);
            return null;
        }

        $company = $data['results'][0];
        $complements = $company['complements'] ?? [];

        // 2. Vérification du statut d'entrepreneur de spectacles vivants
        $isEntrepreneur = $complements['est_entrepreneur_spectacle'] 
                       ?? $complements['spectacle_vivant']['est_entrepreneur_spectacle'] 
                       ?? false;

        if (!$isEntrepreneur) {
            $this->logger->info('SIRET trouvé mais non enregistré comme entrepreneur de spectacles', ['siret' => $siret]);
            return null;
        }

        // 3. Extraction du numéro de récépissé/licence
        $licenceNumber = null;
        $spectacleData = $complements['spectacle_vivant'] ?? [];

        if (isset($spectacleData['numero_recepisse'])) {
            $licenceNumber = $spectacleData['numero_recepisse'];
        } elseif (!empty($spectacleData['licences']) && is_array($spectacleData['licences'])) {
            $licence = current($spectacleData['licences']);
            $licenceNumber = $licence['numero_recepisse'] ?? $licence['numero'] ?? null;
        }

        if (!$licenceNumber) {
            $licenceNumber = 'PLATESV-VALIDE'; 
        }

        $name = $company['nom_complet'] ?? $company['nom_raison_sociale'] ?? 'Structure Inconnue';

        return [
            'name' => $name,
            'business_name' => $name,
            'licence_number' => $licenceNumber,
            'siret' => $siret,
        ];
    }

    private function assertValidSiret(string $siret): void
    {
        if (!preg_match('/^\d{14}$/', $siret)) {
            throw new \InvalidArgumentException(sprintf('Le SIRET "%s" est invalide (14 chiffres attendus).', $siret));
        }
    }
}