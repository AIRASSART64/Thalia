<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

// Service qui exploite l'api entrepreneurs de spectacle du MCC lors de la demande de création d'un nouveau compte utilisateur
class CultureApiService 
{
    // Automatisation de la requête qui permet d'interroger l'api 
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // Récupération depuis l'api de l'organisation par son n° de SIRET
    public function fetchOrganizationBySiret(string $siret): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://data.culture.gouv.fr/api/explore/v2.1/catalog/datasets/declarations-des-entrepreneurs-de-spectacles-vivants/records', [
                'query' => [
                    'where' => sprintf("siren_siret='%s'", $siret),
                    'limit' => 1
                ],
                'timeout' => 3.0,
            ]);

            $data = $response->toArray();

            // Gestion du cas : SIRET inexistant
            if (empty($data['results'])) {
                return null;
            }

            $record = $data['results'][0];

            return [
                'name' => $record['declarant'] ?? 'Structure Inconnue',
                'siret' => $record['siren_siret'] ?? $siret,
                'is_fallback' => false
            ];

        // Gestion du cas : inaccessibilité de l'api du MCC 
        } catch (\Exception $e) {
            return [
                'name' => 'Structure de Test (Mode Local / API déconnectée)',
                'siret' => $siret,
                'is_fallback' => true
            ];
        }
    }
}



