<?php
// ============================================================
// 🔒 CONFIGURAÇÕES PRIVADAS
// Localização: /jslelivr/config_galeria.php
// Este arquivo fica FORA do public_html — não é acessível pela web.
// ============================================================

// --- Login da galeria ---
define('GALLERY_USER',      'admin');
define('GALLERY_PASS_HASH', 'COLE_O_HASH_GERADO_AQUI');  // ← hash gerado pelo gerar_hash.php

// --- GitHub ---
define('GITHUB_TOKEN',  'ghp_SEU_TOKEN_AQUI');
define('GITHUB_USER',   'samucastudent');
define('GITHUB_REPO',   'NOME_DO_REPOSITORIO');
define('GITHUB_BRANCH', 'main');
define('PASTA_DESTINO', 'imagens');