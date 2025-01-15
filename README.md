Aqui está a documentação formatada para o arquivo `README.md` no GitHub:

```markdown
# API - Integração Fiscal

## Descrição
Esta API permite a integração com sistemas fiscais através da validação de CNPJs e geração de tokens seguros. Ela oferece rotas para gerar tokens e processar dados relacionados ao status de CNPJs.

---

## Rotas Disponíveis

### **1. Geração de Token**
**URL:** `/api/geratoken.php`  
**Método:** `POST`  
**Descrição:** Gera um token para autenticação futura, associando um CNPJ, nome e senha.

#### **Cabeçalhos**
Nenhum cabeçalho específico necessário.

#### **Corpo da Requisição (JSON):**
```json
{
    "cnpj": "12345678000195",
    "nome": "Nome da Empresa",
    "senha": "senha123"
}
```

#### **Respostas**
- **200 - Sucesso:**
  ```json
  {
      "success": "Token gerado com sucesso",
      "token": "TOKEN_GERADO_AQUI"
  }
  ```
- **400 - Erro nos Dados:**
  ```json
  {
      "error": "CNPJ inválido ou dados ausentes"
  }
  ```
- **500 - Erro Interno:**
  ```json
  {
      "error": "Erro ao salvar o token no banco"
  }
  ```

---

### **2. Validação de Token e Processamento**
**URL:** `/api/index.php`  
**Método:** `POST`  
**Descrição:** Valida um token previamente gerado e atualiza os dados relacionados ao status do CNPJ (entrada ou saída).

#### **Cabeçalhos**
- **Authorization:** Token gerado anteriormente (formato: Base64).

#### **Corpo da Requisição (JSON):**
```json
{
    "cnpj": "12345678000195",
    "status": "entrada"
}
```

#### **Respostas**
- **200 - Sucesso:**
  ```json
  {
      "success": "Dados salvos com sucesso"
  }
  ```
- **400 - Erro nos Dados:**
  - Quando o CNPJ ou status estão ausentes ou inválidos:
    ```json
    {
        "error": "Dados inválidos"
    }
    ```
  - Quando o JSON está malformado:
    ```json
    {
        "error": "Formato de JSON inválido"
    }
    ```
- **401 - Autenticação Inválida:**
  - Token ausente:
    ```json
    {
        "error": "Token ausente"
    }
    ```
  - Token inválido:
    ```json
    {
        "error": "Token inválido"
    }
    ```
  - CNPJ ou senha inválidos:
    ```json
    {
        "error": "CNPJ ou senha inválidos"
    }
    ```
- **500 - Erro Interno:**
  - Problemas no banco de dados ou outro erro interno:
    ```json
    {
        "error": "Erro ao salvar os dados"
    }
    ```

---

## Fluxo de Autenticação

1. **Geração de Token:**
   - Um token é gerado a partir de um CNPJ, senha e uma chave secreta.
   - O token é codificado em Base64 e contém:
     - **CNPJ**
     - **Senha**
     - **Hash** (HMAC SHA-256) para garantir integridade.

2. **Validação do Token:**
   - O token recebido no cabeçalho é decodificado e seu hash é recalculado com a chave secreta.
   - Caso o hash seja válido, os dados do token são utilizados para acessar o banco.

3. **Atualização de Dados:**
   - A API permite atualizar o status do CNPJ (`entrada` ou `saída`) no banco de dados.

---

## Observações

- A chave secreta usada para gerar e validar tokens deve ser armazenada de forma segura, fora do diretório público.
- As respostas JSON seguem o padrão para indicar sucesso ou erro, permitindo fácil integração com sistemas clientes.
```