<?php

declare(strict_types=1);

namespace Waglpz\Jwt;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

final class JWT
{
    private string $publicKeyFile;

    public function __construct(string $publicKeyFile, private string $algo = 'RS256')
    {
        if (! \is_file($publicKeyFile) || ! \is_readable($publicKeyFile)) {
            throw new \InvalidArgumentException('Public KEY file does not exist or not readable.');
        }

        $fileContent = \file_get_contents($publicKeyFile);
        \assert(\is_string($fileContent));
        $this->publicKeyFile = $fileContent;
    }

    /**
     * Decodes a JWT string into a PHP object.
     *
     * @param string $token The JWT
     *
     * @return \stdClass The JWT's payload as a PHP object
     *
     * @throws \InvalidArgumentException Provided key/key-array was empty or malformed.
     * @throws \DomainException          Provided JWT is malformed.
     * @throws \UnexpectedValueException Provided JWT was invalid.
     * @throws SignatureInvalidException Provided JWT was invalid because the signature verification failed.
     * @throws BeforeValidException      Provided JWT is trying to be used before it's eligible as defined by 'nbf'.
     * @throws BeforeValidException      Provided JWT is trying to be used before it's been created as defined by 'iat'.
     * @throws ExpiredException          Provided JWT has since expired, as defined by the 'exp' claim.
     */
    public function decode(string $token): \stdClass
    {
        $key = new Key($this->publicKeyFile, $this->algo);

        return \Firebase\JWT\JWT::decode($token, $key);
    }
}
