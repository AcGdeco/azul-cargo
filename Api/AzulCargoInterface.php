<?php

namespace Deco\Azulcargo\Api;

/**
 * Interface AzulCargoAuthInterface
 * @api
 */
interface AzulCargoInterface
{
    /**
     * Faz a cotação na API da Azul Cargo.
     *
     * @return string JSON Web Token (JWT) de autenticação.
     */
    public function quote(string $email, string $senha, array $requestData): array;
}