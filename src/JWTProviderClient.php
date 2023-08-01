<?php

declare(strict_types=1);

namespace Waglpz\Jwt;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Phpro\ApiProblem\Http\HttpApiProblem;
use Ramsey\Uuid\Uuid;

final class JWTProviderClient
{
    private string $webappId;
    /** @var array<mixed> */
    private array $initialPayload;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $url,
        string $webappManifestFile,
        private readonly bool $verifySSL = true,
    ) {
        if (! \is_file($webappManifestFile) || ! \is_readable($webappManifestFile)) {
            throw new \Error(\sprintf('Webapp Manifest file "%s" is invalid.', $webappManifestFile));
        }

        try {
            $manifestContent = \file_get_contents($webappManifestFile);
            \assert(\is_string($manifestContent));
            $webappManifestData = \json_decode($manifestContent, false, 512, \JSON_THROW_ON_ERROR);
            \assert($webappManifestData instanceof \stdClass);
        } catch (\Throwable $exception) {
            throw new \Error('Webmanifest file content is invalid must be valid JSON. ' . $exception->getMessage());
        }

        if (
            ! isset($webappManifestData->webappId)
            || ! \is_string($webappManifestData->webappId)
            || ! Uuid::isValid($webappManifestData->webappId)
        ) {
            throw new \Error('Webmanifest file contains invalid WebappID. Expected UUID string Version 4');
        }

        $this->webappId = $webappManifestData->webappId;
    }

    public function fetchJWT(string $accessToken, string $idToken): string|HttpApiProblem
    {
        $payload = [
            'accessToken' => $accessToken,
            'googleToken' => $idToken,
            'webappId'    => $this->webappId,
        ];

        if (isset($this->initialPayload)) {
            $payload += $this->initialPayload;
        }

        try {
            $options      = ['json' => $payload, 'verify' => $this->verifySSL];
            $response     = $this->client->request('POST', $this->url, $options);
            $jsonResponse = (string) $response->getBody();
            $responseData = \json_decode($jsonResponse, true, 5, \JSON_THROW_ON_ERROR);
            \assert(\is_array($responseData));
            if (
                ! isset($responseData['token'])
                || ! \is_string($responseData['token'])
                || $responseData['token'] === ''
            ) {
                throw new \UnexpectedValueException('JWT provider VWD client response has no token.');
            }
        } catch (ClientException) {
            $code = 401;

            return new HttpApiProblem($code, ['detail' => 'Unauthorized by upstream flow.']);
        } catch (\Throwable | ServerException $exception) {
            $code = 500;

            return new HttpApiProblem($code, ['detail' => $exception->getMessage()]);
        }

        return $responseData['token'];
    }

    /** @param array<mixed> $extendData */
    public function initPayloadBeforeSend(array $extendData): void
    {
        $this->initialPayload = $extendData;
    }
}
