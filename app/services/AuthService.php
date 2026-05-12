<?php

class AuthService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(string $email, string $senha): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM usuarios WHERE email = ?"
        );

        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }

        return null;
    }

    public function registrar(string $nome, string $email, string $senha): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return false;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (nome,email,senha,tipo)
             VALUES (?, ?, ?, 'comum')"
        );

        return $stmt->execute([$nome, $email, $senhaHash]);
    }
}