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
    private const API_URL = 'https://data.culture.gouv.fr/api/explore/v2.1/catalog/datasets/declarations-des-entrepreneurs-de-spectacles-vivants/records';

    // Un user ne peut être associé qu'à un établissement qui dispose du statut 'valide' dans l'api.
    private const VALID_STATUSES = ['valide'];

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
                    'where' => sprintf("siren_siret='%s'", $siret),
                    'limit' => 1,
                ],
                'timeout' => 3.0,
            ]);

            $data = $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('API Culture inaccessible (transport)', [
                'siret' => $siret,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception('API Culture inaccessible.', previous: $e);
        } catch (ClientExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface $e) {
            $this->logger->error('API Culture a répondu en erreur', [
                'siret' => $siret,
                'status_code' => $e->getResponse()->getStatusCode(),
            ]);
            throw new \Exception('API Culture a répondu en erreur.', previous: $e);
        } catch (DecodingExceptionInterface $e) {
            $this->logger->error('Réponse API Culture illisible', [
                'siret' => $siret,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception('Réponse API Culture illisible.', previous: $e);
        }

        // Gestion du cas : SIRET inexistant dans la base
        if (empty($data['results'])) {
            return null;
        }

        $record = $data['results'][0];
      

        // Gestion du cas : licence non valide (invalidée, expirée, en instruction)
        $statut = $record['statut'] ?? null;
        $statut = isset($record['statut_recepisse']) ? mb_strtolower($record['statut_recepisse']) : null;
        if (null !== $statut && !in_array($statut, self::VALID_STATUSES, true)) {
            $this->logger->info('SIRET trouvé mais licence non valide', [
                'siret' => $siret,
                'statut' => $statut,
            ]);

            return null;
        }
        $licenceNumber = null;
        if (is_array($record)) {
            $licenceNumber = $record['numero_recepisse'] ?? $record['numero_de_recepisse'] ?? null;
        } elseif (is_object($record)) {
            $licenceNumber = $record->numero_recepisse ?? $record->numero_de_recepisse ?? null;
        }
        if (!$licenceNumber) {
            $licenceNumber = 'PLATESV-R-NOT-FOUND'; 
        }

        return [
            'name' => $record['raison_sociale'] ?? 'Structure Inconnue',
            'business_name' => $record['raison_sociale'] ?? 'Structure Inconnue',
            'licence_number' => $licenceNumber,
            'siret' => $record['siren_siret'] ?? $siret,
            
        ];
    }

    private function assertValidSiret(string $siret): void
    {
        if (!preg_match('/^\d{14}$/', $siret)) {
            throw new \InvalidArgumentException(sprintf('Le SIRET "%s" est invalide (14 chiffres attendus).', $siret));
        }
    }
}