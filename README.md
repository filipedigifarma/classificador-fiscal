# API - Integração Fiscal

## Descrição
Esta API permite a integração com sistemas fiscais, fornecendo validação de CNPJs e geração de tokens seguros. Ela disponibiliza endpoints para geração de tokens e processamento de dados relacionados ao status de CNPJs.

---

## Rotas Disponíveis

### **1. Geração de Token**
**URL BASE:** `https://www.digifarma.com.br/servicos/post/classificador-fiscal`

**ENDPOINT:** `/geratoken`  
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
**ENDPOINT:** `/notifica`  
**Método:** `POST`  
**Descrição:** Valida um token previamente gerado e atualiza os dados relacionados ao status do CNPJ (entrada ou saída).

#### **Cabeçalhos**
- **Authorization:** Token gerado anteriormente (formato: Base64).

#### **Corpo da Requisição (JSON):**
- **Status:** `E` = Entrada, `S` = Saída.
```json
{
    "cnpj": "12345678000195",
    "status": "E"
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
