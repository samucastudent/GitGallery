# 📖 Guia de Instalação e Uso

Passo a passo para instalar o **PHP GitHub Image Host & Gallery** em hospedagem cPanel/WHM. 🚀

---

## 📋 Pré-requisitos

- Hospedagem com **PHP 7.4+** (extensão `cURL` ativada — padrão no cPanel)
- Conta no **GitHub** com repositório público criado
- Acesso ao **Gerenciador de Arquivos** ou **FTP** do cPanel

---

## 🔑 Passo 1: Gerar o Token do GitHub

1. Acesse: [https://github.com/settings/tokens](https://github.com/settings/tokens)
2. Clique em **Generate new token (classic)**
3. Dê um nome (ex: `php-gallery`)
4. Marque a permissão **`repo`** (acesso completo)
5. Clique em **Generate token** e copie o valor (`ghp_...`)

> ⚠️ O token aparece apenas uma vez. Guarde em local seguro!

---

## 🔐 Passo 2: Gerar o Hash da Senha (sem SSH)

Como o cPanel raramente oferece acesso SSH, use o `gerar_hash.php`:

1. Suba o `gerar_hash.php` para dentro do `public_html`
2. Acesse no navegador: `https://seusite.com/gerar_hash.php`
3. Digite a senha que deseja usar e clique em **Gerar Hash**
4. Copie o hash gerado (começa com `$2y$10$...`)
5. **Delete o arquivo `gerar_hash.php` imediatamente!** 🗑️

> ⚠️ Nunca deixe o `gerar_hash.php` acessível por mais tempo do que o necessário.

---

## ⚙️ Passo 3: Configurar o `config_galeria.php`

Edite o arquivo com os dados reais:

```php
define('GALLERY_USER',      'admin');                     // ← seu usuário
define('GALLERY_PASS_HASH', '$2y$10$COLE_O_HASH_AQUI');  // ← hash do passo 2
define('GITHUB_TOKEN',  'ghp_SEU_TOKEN_AQUI');            // ← token do passo 1
define('GITHUB_USER',   'samucastudent');
define('GITHUB_REPO',   'meus-imagens');
define('GITHUB_BRANCH', 'main');
define('PASTA_DESTINO', 'imagens');
```

Salve o arquivo **fora** do `public_html`:
✅ Correto: /gitgallery/config_galeria.php
❌ Errado: /gitgallery/public_html/config_galeria.php


---

## 🚀 Passo 4: Subir os Arquivos

Suba para o servidor via FTP ou Gerenciador de Arquivos:

| Arquivo | Onde salvar |
|---|---|
| `config_galeria.php` | `/gitgallery/` (fora do public_html) |
| `index.php` | `/gitgallery/public_html/` |
| `gerar_hash.php` | `/gitgallery/public_html/` (temporário!) |

Acesse no navegador: `https://seusite.com/` e faça login. 🎉

---

## 🔎 Verificando o Caminho do Config

Se aparecer o erro **"config_galeria.php não encontrado"**, verifique a linha no `index.php`:

```php
// index.php em: public_html/index.php → sobe 1 nível
$config_path = dirname(__DIR__, 1) . '/config_galeria.php';

// index.php em: public_html/galeria/index.php → sobe 2 níveis
$config_path = dirname(__DIR__, 2) . '/config_galeria.php';
```

Ajuste o número conforme a profundidade da pasta onde o `index.php` está.

---

## 🖱️ Como Usar a Galeria

| Ação | Como fazer |
|---|---|
| ⬆️ Enviar imagem | Selecione o arquivo e clique em **Enviar Imagem** |
| 📋 Copiar link CDN | Clique em **Copiar Link** na miniatura |
| 🖼️ Ver original | Clique na miniatura para abrir em nova aba |
| 🚪 Sair | Clique em **Sair** no canto superior direito |

---

## 🔄 Forçar Atualização de Cache (jsDelivr)

Se atualizou uma imagem e ela não mudou no navegador, adicione `?v=2` à URL:

https://cdn.jsdelivr.net/gh/samucastudent/meus-imagens@main/imagens/logo.png?v=2


---

🔙 **[Voltar ao README](README.md)**