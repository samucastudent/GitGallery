# 🖼️ PHP GitHub Image Host & Gallery

Script PHP para hospedar imagens no **GitHub** com galeria visual e links **jsDelivr** prontos para uso. Roda em hospedagens **cPanel/WHM** sem banco de dados. 🚀

---

## ✨ Funcionalidades

- 🔐 **Login protegido** — tela de autenticação com senha hasheada (bcrypt)
- ⬆️ **Upload direto** — envia imagens do navegador para o GitHub via API
- 🖼️ **Galeria automática** — lê o repositório em tempo real, sem banco de dados
- 📋 **Click to Copy** — copia o link jsDelivr com um clique
- ⚡ **CDN global** — imagens servidas pelo jsDelivr, sem consumir banda do servidor

---

## 🗂️ Estrutura no Servidor
/gitgallery/ ← fora do public_html
├── config_galeria.php ← ⚠️ credenciais (nunca acessível pela web)
└── public_html/
├── index.php ← galeria + upload
└── gerar_hash.php ← ⚠️ uso único, delete após usar!

---

## 🗂️ Estrutura do Repositório GitHub
📦 repositório/
├── 📄 README.md
├── 📖 HOW_TO_USE.md
└── 📁 imagens/
├── foto_1710000001.jpg
└── logo_1710000002.png


---

## 🚀 Início Rápido

1. Gere o token em [github.com/settings/tokens](https://github.com/settings/tokens)
2. Suba `gerar_hash.php` no `public_html`, acesse e gere o hash da sua senha
3. Preencha `config_galeria.php` e salve **fora** do `public_html`
4. Suba `index.php` para `public_html` e acesse no navegador

📖 **[Guia detalhado → HOW_TO_USE.md](HOW_TO_USE.md)**

---

Feito com ☕ por [samucastudent](https://github.com/samucastudent)