<?php
session_start();

// ============================================================
// 🔒 Carrega config de fora do public_html
// Estrutura esperada:
//   /jslelivr/public_html/index.php      ← este arquivo
//   /jslelivr/config_galeria.php         ← config aqui
//
// dirname(__DIR__, 1) sobe 1 nível: public_html → jslelivr
// Se index.php estiver em public_html/galeria/, use dirname(__DIR__, 2)
// ============================================================
$config_path = dirname(__DIR__, 1) . '/config_galeria.php';

if (!file_exists($config_path)) {
    die('<p style="font-family:sans-serif;color:red;padding:20px;">
        ❌ <b>config_galeria.php não encontrado.</b><br><br>
        Caminho esperado: <code>' . htmlspecialchars($config_path) . '</code><br><br>
        Verifique se o arquivo está fora do <code>public_html</code>.
    </p>');
}
require_once $config_path;

// ============================================================
// 🔐 SISTEMA DE LOGIN
// ============================================================
$erro_login = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_user'])) {
    $user = trim($_POST['login_user'] ?? '');
    $pass = $_POST['login_pass'] ?? '';
    if ($user === GALLERY_USER && password_verify($pass, GALLERY_PASS_HASH)) {
        $_SESSION['galeria_autenticado'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $erro_login = '❌ Usuário ou senha incorretos.';
    }
}

if (empty($_SESSION['galeria_autenticado'])) {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Login — Galeria</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 360px; }
        h2 { text-align: center; margin-top: 0; color: #333; }
        label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; margin-bottom: 16px; }
        input:focus { outline: none; border-color: #2ea44f; box-shadow: 0 0 0 3px rgba(46,164,79,0.2); }
        button { width: 100%; padding: 12px; background: #2ea44f; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background: #22863a; }
        .erro { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 16px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Galeria Privada</h2>
        <?php if ($erro_login): ?>
            <div class="erro"><?= $erro_login ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="login_user">Usuário</label>
            <input type="text" id="login_user" name="login_user" autocomplete="username" required autofocus>
            <label for="login_pass">Senha</label>
            <input type="password" id="login_pass" name="login_pass" autocomplete="current-password" required>
            <button type="submit">Entrar →</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// ============================================================
// Usuário autenticado — continua
// ============================================================
$mensagem      = "";
$link_jsdelivr = "";

// ============================================================
// 🚀 PROCESSAMENTO DO UPLOAD
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    $arquivo = $_FILES['imagem'];
    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $extensao   = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $nome_base  = preg_replace('/[^a-zA-Z0-9-_]/', '', pathinfo($arquivo['name'], PATHINFO_FILENAME));
        $nome_final = $nome_base . '_' . time() . '.' . $extensao;

        $conteudo        = file_get_contents($arquivo['tmp_name']);
        $conteudo_base64 = base64_encode($conteudo);
        $caminho_remoto  = PASTA_DESTINO . '/' . $nome_final;
        $url             = "https://api.github.com/repos/" . GITHUB_USER . "/" . GITHUB_REPO . "/contents/$caminho_remoto";

        $payload = json_encode([
            "message" => "Upload via PHP: $nome_final",
            "content" => $conteudo_base64,
            "branch"  => GITHUB_BRANCH
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . GITHUB_TOKEN,
            "User-Agent: PHP-Image-Uploader",
            "Content-Type: application/json",
            "Accept: application/vnd.github.v3+json"
        ]);

        $resposta  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 201 || $http_code == 200) {
            $link_jsdelivr = "https://cdn.jsdelivr.net/gh/" . GITHUB_USER . "/" . GITHUB_REPO . "@" . GITHUB_BRANCH . "/$caminho_remoto";
            $mensagem = "<div class='success'>✅ Upload de <b>$nome_final</b> realizado com sucesso!</div>";
        } else {
            $erro_detalhe = json_decode($resposta, true);
            $mensagem = "<div class='error'>❌ Erro no upload. (HTTP $http_code) — " . htmlspecialchars($erro_detalhe['message'] ?? 'Erro desconhecido') . "</div>";
        }
    } else {
        $mensagem = "<div class='error'>❌ Erro ao receber o arquivo. Código: " . $arquivo['error'] . "</div>";
    }
}

// ============================================================
// 🖼️ BUSCAR IMAGENS PARA A GALERIA
// ============================================================
$url_galeria = "https://api.github.com/repos/" . GITHUB_USER . "/" . GITHUB_REPO . "/contents/" . PASTA_DESTINO;
$ch_gal = curl_init($url_galeria);
curl_setopt($ch_gal, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_gal, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . GITHUB_TOKEN,
    "User-Agent: PHP-Image-Uploader",
    "Accept: application/vnd.github.v3+json"
]);
$resposta_galeria = curl_exec($ch_gal);
$http_code_gal    = curl_getinfo($ch_gal, CURLINFO_HTTP_CODE);
curl_close($ch_gal);

$arquivos_github = [];
if ($http_code_gal == 200) {
    $dados = json_decode($resposta_galeria, true);
    if (is_array($dados)) {
        usort($dados, fn($a, $b) => strcmp($b['name'], $a['name']));
        foreach ($dados as $item) {
            if ($item['type'] === 'file') {
                $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $item['jsdelivr'] = "https://cdn.jsdelivr.net/gh/" . GITHUB_USER . "/" . GITHUB_REPO . "@" . GITHUB_BRANCH . "/{$item['path']}";
                    $arquivos_github[] = $item;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Image Host & Gallery</title>
    <style>
        :root { --primary: #2ea44f; --bg: #f4f7f6; --text: #333; --card: #fff; }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .topbar { width: 100%; max-width: 1000px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .topbar span { font-size: 14px; color: #555; }
        .topbar a { font-size: 13px; color: #666; text-decoration: none; background: #eee; padding: 5px 14px; border-radius: 20px; }
        .topbar a:hover { background: #ddd; color: #333; }
        .container { background: var(--card); padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; margin-bottom: 30px; }
        h2 { text-align: center; margin-top: 0; }
        input[type="file"] { width: 100%; padding: 10px; border: 2px dashed #ccc; border-radius: 6px; background: #fafafa; cursor: pointer; margin-bottom: 16px; }
        button[type="submit"] { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button[type="submit"]:hover { background: #22863a; }
        .success, .error { padding: 15px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .result-box { background: #e9ecef; border: 1px solid #ced4da; border-radius: 6px; padding: 15px; margin-bottom: 16px; }
        .result-box label { font-size: 13px; font-weight: bold; display: block; margin: 10px 0 4px; }
        .result-box input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 12px; background: #fff; }
        .img-preview { max-width: 100%; margin-top: 12px; border-radius: 6px; border: 1px solid #ddd; display: block; }
        .gallery-container { width: 100%; max-width: 1000px; }
        .gallery-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .gallery-header h2 { margin: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .card { background: var(--card); border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .card-img { width: 100%; height: 150px; object-fit: cover; background: #eee; border-bottom: 1px solid #eee; display: block; }
        .card-body { padding: 12px; flex-grow: 1; display: flex; flex-direction: column; gap: 8px; }
        .card-title { font-size: 12px; word-break: break-all; color: #555; }
        .btn-copy { background: #0366d6; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-size: 12px; width: 100%; transition: background 0.2s; }
        .btn-copy:hover { background: #005cc5; }
        .empty-msg { text-align: center; color: #777; padding: 40px; background: var(--card); border-radius: 8px; }
    </style>
</head>
<body>

<div class="topbar">
    <span>👤 Olá, <b><?= htmlspecialchars(GALLERY_USER) ?></b></span>
    <a href="?logout=1">🚪 Sair</a>
</div>

<!-- UPLOAD -->
<div class="container">
    <h2>🚀 Upload para o GitHub</h2>
    <?= $mensagem ?>
    <?php if ($link_jsdelivr): ?>
    <div class="result-box">
        <label>🔗 Link jsDelivr:</label>
        <input type="text" value="<?= htmlspecialchars($link_jsdelivr) ?>" onclick="this.select();" readonly>
        <label>📝 Markdown:</label>
        <input type="text" value="![Imagem](<?= htmlspecialchars($link_jsdelivr) ?>)" onclick="this.select();" readonly>
        <label>🖼️ HTML:</label>
        <input type="text" value='<img src="<?= htmlspecialchars($link_jsdelivr) ?>" alt="Imagem">' onclick="this.select();" readonly>
        <img src="<?= htmlspecialchars($link_jsdelivr) ?>" class="img-preview" alt="Preview">
    </div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="imagem" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" required>
        <button type="submit">⬆️ Enviar Imagem</button>
    </form>
</div>

<!-- GALERIA -->
<div class="gallery-container">
    <div class="gallery-header">
        <h2>🖼️ Galeria</h2>
        <span style="font-size:14px;color:#666;">📦 <?= count($arquivos_github) ?> imagem(ns)</span>
    </div>
    <?php if (empty($arquivos_github)): ?>
        <div class="empty-msg">😕 Nenhuma imagem em <b><?= htmlspecialchars(PASTA_DESTINO) ?></b>.<br>Faça o primeiro upload acima!</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($arquivos_github as $img): ?>
            <div class="card">
                <a href="<?= htmlspecialchars($img['jsdelivr']) ?>" target="_blank">
                    <img src="<?= htmlspecialchars($img['jsdelivr']) ?>" class="card-img" loading="lazy" alt="<?= htmlspecialchars($img['name']) ?>">
                </a>
                <div class="card-body">
                    <div class="card-title" title="<?= htmlspecialchars($img['name']) ?>">
                        <?= htmlspecialchars(strlen($img['name']) > 28 ? substr($img['name'], 0, 25) . '...' : $img['name']) ?>
                    </div>
                    <button class="btn-copy" onclick="copiar('<?= htmlspecialchars($img['jsdelivr']) ?>', this)">📋 Copiar Link</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function copiar(texto, btn) {
    navigator.clipboard.writeText(texto).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '✅ Copiado!';
        btn.style.background = '#2ea44f';
        setTimeout(() => { btn.innerHTML = orig; btn.style.background = '#0366d6'; }, 2000);
    }).catch(() => {
        const el = document.createElement('textarea');
        el.value = texto;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        btn.innerHTML = '✅ Copiado!';
        setTimeout(() => { btn.innerHTML = '📋 Copiar Link'; }, 2000);
    });
}
</script>
</body>
</html>