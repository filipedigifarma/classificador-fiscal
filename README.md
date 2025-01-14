# Documentação da API de Integração Fiscal

## Introdução
Esta API permite que empresas parceiras enviem notificações sobre CNPJs que entraram ou saíram de suas bases. Ela é composta por três principais endpoints: geração de token, validação de token, e registro de notificações.

## Endpoints

### 1. Gerar Token
**Rota:** `POST /geratoken.php`

**Descrição:** Gera um token único para a empresa com base no CNPJ e salva no banco de dados.

**Parâmetros de Entrada:**
- **cnpj** (string): CNPJ da empresa parceira (14 dígitos).
- **senha** (string): Senha da empresa.

**Exemplo de Requisição:**
```json
{
  "cnpj": "12345678000195",
  "senha": "senha123"
}
```

**Exemplo de Resposta (Sucesso):**
```json
{
  "success": "Token gerado com sucesso",
  "token": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
}
```

**Códigos de Resposta:**
- **200:** Token gerado com sucesso.
- **400:** Dados inválidos (CNPJ ou senha ausentes).
- **500:** Erro ao salvar no banco de dados.

---

### 2. Validar Token
**Rota:** `POST /validar-token.php`

**Descrição:** Verifica se o token fornecido é válido e está associado a um CNPJ no banco de dados.

**Cabeçalhos:**
- **Authorization** (string): Token gerado previamente.

**Exemplo de Requisição:**
```http
Authorization: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
```

**Exemplo de Resposta (Sucesso):**
```json
{
  "success": "Token válido",
  "cnpj": "12345678000195"
}
```

**Códigos de Resposta:**
- **200:** Token válido.
- **401:** Token inválido ou ausente.

---

### 3. Registrar Notificação
**Rota:** `POST /index.php`

**Descrição:** Registra notificações de entrada ou saída de CNPJs enviados pelas empresas parceiras.

**Cabeçalhos:**
- **Authorization** (string): Token gerado previamente.

**Parâmetros de Entrada:**
- **cnpj** (string): CNPJ notificado.
- **status** (string): Indica se o CNPJ está entrando ou saindo (valores permitidos: `entrada`, `saida`).

**Exemplo de Requisição:**
```json
{
  "cnpj": "98765432000145",
  "status": "entrada"
}
```

**Exemplo de Resposta (Sucesso):**
```json
{
  "success": "CNPJ registrado com sucesso"
}
```

**Códigos de Resposta:**
- **200:** Dados salvos com sucesso.
- **400:** Dados inválidos (CNPJ ou status ausentes).
- **401:** Token inválido ou ausente.
- **500:** Erro ao salvar os dados no banco de dados.

---

## Observações Finais
- Certifique-se de que todas as conexões usem HTTPS.
- As respostas da API estão padronizadas em JSON para facilitar a integração.
