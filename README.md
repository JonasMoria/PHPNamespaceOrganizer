
# 🧩 Namespace & USE Statement Fixer

Este projeto contém dois scripts utilitários escritos em PHP 7.0 para organizar e padronizar namespaces e `use` statements no seu monolito PHP.

---

## 📂 Estrutura
Para executar você deve inserir os scripts dentro da pasta `NamespaceUseStatementFixer` na raiz do seu projeto
```
/NamespaceUseStatementFixer
  ├── fix-namespaces-and-uses-by-classes.php
  └── make-classes-statistics.php
```

---

## ✅ Requisitos

- PHP **7.0 ou superior**
- Projeto com estrutura baseada em pastas (monolito)

---

## 🔧 Script: `fix-namespaces-and-uses-by-classes.php`

### 📌 O que ele faz:

- Adiciona ou atualiza o `namespace` de classes especificadas.
- Corrige ou insere os `use` statements corretos em **todos os arquivos do projeto** onde a(s) classe(s) especificada(s) é(são) usada(s).
- Garante que os `use` tenham o caminho completo, como, por exemplo:
  `use App\Classes\User\UserClass`

---

### 🛠️ Como configurar

Edite a variável `$baseNamespace` dentro do script para informar o nome base que os USE's Statements irão conter, pode ser o nome do seu projeto ou da pasta onde as classes estão contidas:

```php
$baseNamespace = 'MeuProjeto';
// ou
$baseNamespace = 'App';
```
Edite a variável `$classes` dentro do script para informar as classes a serem processadas:

```php
$classes = [
    'App/Classes/User/UserClass',
    'App/Classes/Client/ClientClass'
];
```

> **Importante:**  
> Caminhos são relativos à raiz do projeto. Não inclua a extensão `.php`.

Caso deseje, edite a variável `$ignoreDirs` dentro do script para adicionar diretorios onde as classes não deverão ser processadas:

```php
$ignoreDirs = ['vendor', 'node_modules'];
```

---

### ▶️ Como executar

No terminal, na raiz do seu projeto, execute:

```bash
php NamespaceUseStatementFixer/fix-namespaces-and-uses-by-classes.php
```

---

### ✅ Exemplo de transformação

#### Antes (`App/Classes/User/UserClass.php`):

```php
<?php

class UserClass {
    // ...
}
```

#### Depois:

```php
<?php
namespace App\Classes\User;

class UserClass {
    // ...
}
```

#### Em outro arquivo (`controller.php`) que usa essa classe:

##### Antes:

```php
<?php

$user = new UserClass();
```

##### Depois:

```php
<?php
use App\Classes\User\UserClass;

$user = new UserClass();
```

---

## 📊 Script: `make-classes-statistics.php`

### 📌 O que ele faz:

- Mapeia todas as **classes** definidas no diretório especificado em `$baseDir`
- Conta quantas **possuem** `namespace` e quantas **não possuem**`

---

### ▶️ Como executar

```bash
php NamespaceUseStatementFixer/make-classes-statistics.php
```

---

### ✅ Exemplo de saída

```
Estatísticas de Classes:
---------------------------
Total de classes encontradas:       142
Com namespace declarado:            107
Sem namespace declarado:            35
```

---

## ⚠️ Observações

- Classes que já possuem `use` correto não serão alteradas.
- `use` não é adicionado na própria classe.
- `require/include` são ignorados para não gerar falsos positivos.

---

## 💬 Sugestões

- Use o `make-classes-statistics.php` para acompanhar a migração de classes legadas para o padrão PSR-4.
- Execute frequentemente em um ambiente de testes antes de aplicar em produção.