<?php

declare(strict_types=1);

namespace App\Security\Provider;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;

final class JwtMercureProvider
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function __invoke(): string
    {
        return strval((new Builder())
            ->withClaim('mercure', ['publish' => ['*']])
            ->getToken(new Sha256(), new Key($this->secret)));
    }
}