<?php

namespace Deco\Azulcargo\Api;

/**
 * Interface AzulCargoAuthInterface
 * @api
 */
interface AzulCargoAuthInterface
{
    /**
     * Autentica um usuário na API da Azul Cargo.
     *
     * @param string $email
     * @param string $senha
     * @return string JSON Web Token (JWT) de autenticação.
     */
    public function authenticateUser(string $email, string $senha): string;
}