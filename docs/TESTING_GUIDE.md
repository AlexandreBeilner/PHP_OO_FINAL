# Guia de Testes de Funcionamento - Projeto PHP-OO

## 📋 **Visão Geral**

Este guia apresenta um **passo a passo sequencial** para testar o funcionamento completo da infraestrutura do projeto PHP-OO, desde a inicialização dos containers até a validação final de todos os serviços.

> **📁 Pré-requisito:** Execute todos os comandos a partir do diretório `project_phpoo_final` do seu workspace.

---

## 🚀 **PASSO A PASSO COMPLETO DE TESTES**

### **PASSO 1: Inicialização da Infraestrutura**

#### 1.1 Subir os Containers
```bash
# Subir todos os containers
docker compose up -d
```

**⏱️ Tempo esperado:** 2-3 minutos para inicialização completa

#### 1.2 Verificar Status dos Containers
```bash
# Verificar se todos os containers estão UP
docker compose ps
```

**✅ Resultado Esperado:**
- Todos os containers com status "Up"
- Nenhum container com status "Exited" ou "Restarting"

**🔍 Verificação Rápida:**
```bash
# Contar containers UP
docker compose ps | grep "Up" | wc -l
# Deve retornar 18 (containers ativos)
# Nota: Containers php-cli saem automaticamente após execução
```

---

### **PASSO 2: Verificação de Portas e Conectividade**

#### 2.1 Verificar Portas Expostas
```bash
# Verificar se as portas estão abertas
netstat -tlnp | grep -E "(8080|8404|11211)"
```

**✅ Portas Esperadas:**
- `8080`: Nginx Load Balancer (acesso principal)
- `8404`: HAProxy Stats (monitoramento)
- `11211`: Memcached (cache de sessões)

#### 2.2 Teste de Conectividade Básica
```bash
# Testar se o servidor web está respondendo
curl -I http://localhost:8080/
```

**✅ Resultado Esperado:**
```http
HTTP/1.1 200 OK
Server: nginx/1.29.1
Content-Type: text/html; charset=UTF-8
```

---

### **PASSO 3: Teste do Endpoint Principal**

#### 3.1 Teste Básico do index.php
```bash
# Fazer requisição completa
curl -v http://localhost:8080/
```

**✅ Resultado Esperado:**
```html
Hello World!<br />PHP Version: [versão]<br />Session ID: [id]<br />Valor salvo na sessão: [número]
```

#### 3.2 Verificar Headers HTTP
```bash
# Verificar headers de resposta
curl -I http://localhost:8080/
```

**✅ Headers Esperados:**
- `HTTP/1.1 200 OK`
- `Server: nginx/1.29.1`
- `Content-Type: text/html; charset=UTF-8`
- `Set-Cookie: PHPSESSID=[id]; path=/`

#### 3.3 Teste de Sessões (Memcached)
```bash
# Primeira requisição
echo "=== Primeira Requisição ==="
curl -s http://localhost:8080/ | grep -E "(Session ID|Valor salvo)"

# Segunda requisição (deve manter sessão)
echo "=== Segunda Requisição ==="
curl -s http://localhost:8080/ | grep -E "(Session ID|Valor salvo)"
```

**✅ Resultado Esperado:**
- Session ID pode ser diferente (load balancing)
- Valor da sessão deve ser persistido
- Memcached funcionando corretamente

---

### **PASSO 4: Teste do Endpoint de Banco de Dados**

#### 4.1 Teste Completo do test_database.php
```bash
# Fazer requisição completa
curl -v http://localhost:8080/test_database.php
```

**✅ Resultado Esperado:**
```html
<h1>Teste PostgreSQL com PDO</h1>
<h3>Primary Database</h3>
✅ Conectado (Primary)<br>
✅ Escrita OK<br>
✅ Leitura OK ([X] registros)<br>

<h3>HAProxy Database</h3>
✅ Conectado (Primary)<br>
⚠️ Escrita bloqueada (read-only)<br>
✅ Leitura OK ([X] registros)<br>

<p>PHP [versão] | PDO PostgreSQL: OK | [timestamp]</p>
```

#### 4.2 Verificação Rápida de Banco
```bash
# Verificar apenas os status de conexão
curl -s http://localhost:8080/test_database.php | grep -E "(Conectado|Escrita|Leitura|Erro)"
```

**✅ Resultado Esperado:**
- ✅ Conectado (Primary) - PostgreSQL Primary funcionando
- ✅ Escrita OK - Operações de INSERT funcionando
- ✅ Leitura OK - Operações de SELECT funcionando
- ⚠️ Escrita bloqueada (read-only) - HAProxy redirecionando para slave

---

### **PASSO 5: Teste de Load Balancing**

#### 5.1 Verificar Distribuição de Versões PHP
```bash
# Fazer 10 requisições para ver distribuição
echo "=== Teste de Load Balancing ==="
for i in {1..10}; do
  echo -n "Requisição $i: "
  curl -s http://localhost:8080/ | grep "PHP Version" | sed 's/<br \/>//'
  sleep 0.5
done
```

**✅ Resultado Esperado:**
- Diferentes versões PHP aparecendo (7.4, 8.0, 8.1, 8.2, 8.3, 8.4)
- Distribuição relativamente uniforme

#### 5.2 Teste de Performance
```bash
# Teste de carga simples
echo "=== Teste de Performance ==="
time for i in {1..20}; do curl -s http://localhost:8080/ > /dev/null; done
```

**✅ Resultado Esperado:**
- Tempo total < 10 segundos para 20 requisições
- Sem erros de timeout

---

### **PASSO 6: Verificação de Serviços de Banco**

#### 6.1 Teste de PostgreSQL Primary
```bash
# Testar conexão direta com Primary
echo "=== Teste PostgreSQL Primary ==="
docker exec $(docker compose ps -q postgres_primary) psql -U postgres -d phpoo_app -c "SELECT version();" 2>/dev/null | head -1
```

#### 6.2 Teste de PostgreSQL Slaves
```bash
# Testar conexão com Slaves
echo "=== Teste PostgreSQL Slaves ==="
docker exec $(docker compose ps -q postgres_slave1) psql -U postgres_slave -d phpoo_app -c "SELECT pg_is_in_recovery();" 2>/dev/null
docker exec $(docker compose ps -q postgres_slave2) psql -U postgres_slave -d phpoo_app -c "SELECT pg_is_in_recovery();" 2>/dev/null
```

**✅ Resultado Esperado:**
- Primary: `f` (false - não é replica)
- Slaves: `t` (true - são replicas)

#### 6.3 Teste de HAProxy Stats
```bash
# Verificar interface de estatísticas do HAProxy
echo "=== Teste HAProxy Stats ==="
curl -s http://localhost:8404/stats | head -5
```

**✅ Resultado Esperado:**
- Interface de estatísticas acessível
- Dados de conexões visíveis

---

### **PASSO 7: Teste de Cache (Memcached)**

#### 7.1 Verificar Status do Memcached
```bash
# Verificar se Memcached está funcionando
echo "=== Teste Memcached ==="
docker exec $(docker compose ps -q memcached) memcached-tool localhost:11211 stats 2>/dev/null | head -3
```

**✅ Resultado Esperado:**
- Estatísticas do Memcached visíveis
- Serviço funcionando

---

### **PASSO 8: Validação Final**

#### 8.1 Checklist Completo
```bash
echo "=== VALIDAÇÃO FINAL ==="
echo "1. Containers UP:"
docker compose ps | grep "Up" | wc -l
echo "2. Porta 8080 (Nginx):"
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/
echo "3. Porta 8404 (HAProxy):"
curl -s -o /dev/null -w "%{http_code}" http://localhost:8404/stats
echo "4. Endpoint principal:"
curl -s http://localhost:8080/ | grep -q "Hello World" && echo "OK" || echo "ERRO"
echo "5. Endpoint banco:"
curl -s http://localhost:8080/test_database.php | grep -q "Conectado" && echo "OK" || echo "ERRO"
```

**✅ Resultado Esperado:**
- 18 containers UP
- HTTP 200 para ambas as portas
- "OK" para ambos os endpoints

#### 8.2 Resumo de Status
```bash
echo "=== RESUMO DE STATUS ==="
echo "✅ Infraestrutura: $(docker compose ps | grep "Up" | wc -l)/18 containers"
echo "✅ Web Server: $(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/)"
echo "✅ Database: $(curl -s http://localhost:8080/test_database.php | grep -c "✅") conexões OK"
echo "✅ Load Balancing: $(curl -s http://localhost:8080/ | grep -o "PHP Version: [0-9.]*" | head -1)"
```

---

## 🔧 **Health Checks dos Serviços**

> **📋 Guia Completo:** Para informações detalhadas sobre health checks, consulte o arquivo `docs/HEALTH_CHECKS_GUIDE.md`

### **Status Rápido dos Health Checks**

```bash
# Ver status de todos os containers
docker compose ps

# Ver apenas containers healthy
docker compose ps | grep "healthy"

# Ver apenas containers unhealthy
docker compose ps | grep "unhealthy"
```

### **Verificação Rápida**

```bash
# Verificar se todos os serviços principais estão healthy
echo "=== Status dos Health Checks ==="
echo "Nginx LB: $(docker inspect $(docker compose ps -q nginx_lb) | jq -r '.[0].State.Health.Status')"
echo "PostgreSQL Primary: $(docker inspect $(docker compose ps -q postgres_primary) | jq -r '.[0].State.Health.Status')"
echo "HAProxy: $(docker inspect $(docker compose ps -q haproxy) | jq -r '.[0].State.Health.Status')"
echo "Memcached: $(docker inspect $(docker compose ps -q memcached) | jq -r '.[0].State.Health.Status')"
```

**✅ Resultado Esperado:**
- Todos os status devem mostrar "healthy"
- Nenhum status deve mostrar "unhealthy" ou "starting"

---

## 🚨 **Solução de Problemas Comuns**

### Problema: HAProxy não consegue resolver hostnames
**Sintoma:** `could not translate host name "postgres_slave1" to address`
**Solução:** 
```bash
docker compose restart postgres_slave1
docker compose restart haproxy
```

### Problema: Containers não iniciam
**Sintoma:** Containers com status "Exited"
**Solução:**
```bash
docker compose down
docker compose up -d
```

### Problema: Porta 8080 não responde
**Sintoma:** `Connection refused` na porta 8080
**Solução:**
```bash
docker compose restart nginx_lb
```

---

## ✅ **Checklist Rápido**

- [ ] `docker compose up -d` executado
- [ ] Todos os containers UP (18/18)
- [ ] Porta 8080 respondendo
- [ ] `curl http://localhost:8080/` retorna "Hello World"
- [ ] `curl http://localhost:8080/test_database.php` retorna "Conectado"
- [ ] Load balancing funcionando (diferentes versões PHP)
- [ ] Sessões persistindo (Memcached)

---

## 📝 **Notas Técnicas**

- **Tempo total de teste:** ~5 minutos
- **Containers:** 18 (6 php-cli que só sobem quando necessário) serviços (PHP multi-versão, PostgreSQL, Nginx, HAProxy, Memcached)
- **Portas:** 8080 (web), 8404 (stats), 11211 (cache)
- **PHP:** Versões 7.4 a 8.4 com load balancing
- **Banco:** PostgreSQL com replicação master-slave
- **Cache:** Memcached para sessões PHP
- **Compatibilidade:** Funciona em qualquer ambiente (caminhos relativos)

---

*Guia atualizado em 2025-09-10 - Infraestrutura testada e funcionando*
