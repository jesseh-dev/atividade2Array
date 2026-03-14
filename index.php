<?php
// ── Sessão & estado ──────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

foreach (['nomes','categorias','quantidades','precos'] as $k)
    if (!isset($_SESSION[$k])) $_SESSION[$k] = [];

$n   = &$_SESSION['nomes'];
$cat = &$_SESSION['categorias'];
$qtd = &$_SESSION['quantidades'];
$prc = &$_SESSION['precos'];
$MAX = 10;

$acao = $_POST['acao'] ?? 'menu';
$msg  = '';

// ── Ações ────────────────────────────────────────────────────────────────────
if ($acao === 'cadastrar_post') {
    $nome  = trim($_POST['nome']  ?? '');
    $c     = trim($_POST['categoria'] ?? '');
    $q     = (int)($_POST['quantidade'] ?? 0);
    $p     = (float)str_replace(',', '.', $_POST['preco'] ?? '0');

    if ($nome && $c && $q >= 0 && $p >= 0) {
        if (count($n) < $MAX) {
            $n[] = $nome; $cat[] = $c; $qtd[] = $q; $prc[] = $p;
            $msg  = "✓ Produto <strong>$nome</strong> cadastrado!";
            $acao = 'menu';
        } else { $msg = "✗ Limite de $MAX produtos atingido."; $acao = 'cadastrar'; }
    } else { $msg = "✗ Preencha todos os campos corretamente."; $acao = 'cadastrar'; }
}

if ($acao === 'buscar_post') $acao = 'buscar_resultado';
if ($acao === 'sair')        session_destroy();

function moeda($v) { return 'R$ ' . number_format($v, 2, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Controle de Produtos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{
  --glass:rgba(255,255,255,0.55);
  --glass-hover:rgba(255,255,255,0.75);
  --brd:rgba(255,255,255,0.6);
  --brd-soft:rgba(180,180,220,0.25);
  --acc:#6c63ff;
  --acc2:#a78bfa;
  --txt:#1e1b4b;
  --mut:#6b7280;
  --ok:#059669;
  --err:#dc2626;
  --r:16px;
  --shadow:0 8px 32px rgba(99,88,220,0.10),0 1.5px 6px rgba(99,88,220,0.07);
  --shadow-card:0 4px 24px rgba(99,88,220,0.13),0 1px 4px rgba(99,88,220,0.06);
}
*{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Syne',sans-serif;
  min-height:100vh;
  padding:2.5rem 1rem;
  color:var(--txt);
  background:
    radial-gradient(ellipse at 15% 20%, rgba(167,139,250,0.28) 0%, transparent 55%),
    radial-gradient(ellipse at 85% 75%, rgba(99,102,241,0.22) 0%, transparent 55%),
    radial-gradient(ellipse at 50% 50%, rgba(224,231,255,0.5) 0%, transparent 80%),
    #f0f0ff;
  background-attachment:fixed;
}
.wrap{max-width:780px;margin:0 auto}

/* Blobs decorativos */
body::before,body::after{
  content:'';position:fixed;border-radius:50%;filter:blur(60px);pointer-events:none;z-index:0;
}
body::before{width:380px;height:380px;background:rgba(167,139,250,0.25);top:-80px;left:-100px;}
body::after{width:320px;height:320px;background:rgba(99,102,241,0.18);bottom:-60px;right:-80px;}
.wrap{position:relative;z-index:1}

/* Header */
header{
  display:flex;align-items:center;gap:1rem;
  margin-bottom:2.5rem;padding-bottom:1.5rem;
  border-bottom:1px solid var(--brd-soft);
}
.logo{
  width:52px;height:52px;flex-shrink:0;
  background:linear-gradient(135deg,#a78bfa,#6c63ff);
  border-radius:14px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;
  box-shadow:0 4px 16px rgba(108,99,255,0.35);
}
header h1{font-size:1.35rem;font-weight:800;line-height:1.25;color:var(--txt)}
header h1 span{
  background:linear-gradient(90deg,#6c63ff,#a78bfa);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.badge{
  margin-left:auto;
  font-family:'JetBrains Mono',monospace;font-size:.75rem;
  background:var(--glass);
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  border:1px solid var(--brd);
  padding:.3rem .8rem;border-radius:99px;color:var(--mut);
}
.badge strong{color:var(--acc)}

/* Mensagens */
.msg{
  padding:.85rem 1.2rem;border-radius:12px;
  margin-bottom:1.5rem;font-size:.9rem;border-left:3px solid;
  backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);
}
.msg.ok{background:rgba(5,150,105,0.08);border-color:var(--ok);color:var(--ok)}
.msg.err{background:rgba(220,38,38,0.08);border-color:var(--err);color:var(--err)}

/* Card Glass */
.card{
  background:var(--glass);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--brd);
  border-radius:var(--r);
  padding:1.8rem;margin-bottom:1.2rem;
  box-shadow:var(--shadow-card);
}
.card h2{font-size:1.05rem;font-weight:700;margin-bottom:1.4rem;display:flex;align-items:center;gap:.5rem;color:var(--txt)}
.card h2 .ic{font-size:1.1rem}

/* Grid */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}

/* Botões do menu */
.menu-btn{
  background:rgba(255,255,255,0.45);
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border:1px solid var(--brd);
  border-radius:12px;color:var(--txt);
  font-family:'Syne',sans-serif;font-size:.92rem;font-weight:600;
  padding:1rem 1.1rem;cursor:pointer;text-align:left;
  transition:background .2s,box-shadow .2s,transform .15s;
  display:flex;align-items:center;gap:.7rem;width:100%;
}
.menu-btn:hover{
  background:var(--glass-hover);
  box-shadow:0 4px 20px rgba(108,99,255,0.18);
  transform:translateY(-1px);
}
.menu-btn.danger:hover{box-shadow:0 4px 16px rgba(220,38,38,0.18);border-color:rgba(220,38,38,0.4)}
.num{
  font-family:'JetBrains Mono',monospace;font-size:.78rem;
  background:linear-gradient(135deg,rgba(108,99,255,0.15),rgba(167,139,250,0.2));
  color:var(--acc);padding:.18rem .5rem;border-radius:6px;
  border:1px solid rgba(108,99,255,0.2);
}
.danger .num{background:rgba(220,38,38,0.08);color:var(--err);border-color:rgba(220,38,38,0.2)}

/* Formulário */
.row{margin-bottom:1rem}
.row label{
  display:block;font-size:.75rem;font-weight:700;color:var(--mut);
  text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;
}
.row input,.row select{
  width:100%;
  background:rgba(255,255,255,0.6);
  border:1px solid rgba(180,180,220,0.4);
  border-radius:10px;color:var(--txt);
  font-family:'JetBrains Mono',monospace;font-size:.88rem;
  padding:.65rem .9rem;outline:none;
  transition:border-color .2s,box-shadow .2s;
}
.row input:focus,.row select:focus{
  border-color:var(--acc2);
  box-shadow:0 0 0 3px rgba(108,99,255,0.12);
}
.row select option{background:#fff}

/* Botão primário */
.btn{
  background:linear-gradient(135deg,#6c63ff,#a78bfa);
  color:#fff;border:none;border-radius:10px;
  font-family:'Syne',sans-serif;font-weight:700;font-size:.92rem;
  padding:.72rem 1.5rem;cursor:pointer;
  transition:opacity .2s,transform .15s,box-shadow .2s;
  margin-top:.5rem;
  box-shadow:0 4px 16px rgba(108,99,255,0.35);
}
.btn:hover{opacity:.9;transform:translateY(-1px);box-shadow:0 6px 20px rgba(108,99,255,0.4)}

/* Botão voltar */
.btn-back{
  background:rgba(255,255,255,0.45);
  border:1px solid var(--brd);border-radius:10px;color:var(--mut);
  font-family:'Syne',sans-serif;font-size:.83rem;
  padding:.6rem 1rem;cursor:pointer;margin-left:.6rem;
  transition:background .2s,color .2s;
}
.btn-back:hover{background:var(--glass-hover);color:var(--txt)}

/* Tabela */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:.87rem}
thead tr{border-bottom:1.5px solid rgba(108,99,255,0.25)}
thead th{
  font-family:'JetBrains Mono',monospace;font-size:.72rem;
  text-transform:uppercase;letter-spacing:.06em;color:var(--mut);
  padding:.5rem .7rem;text-align:left;
}
tbody tr{border-bottom:1px solid rgba(180,180,220,0.2);transition:background .15s}
tbody tr:hover{background:rgba(255,255,255,0.45)}
tbody td{padding:.65rem .7rem}
.tag{
  display:inline-block;font-family:'JetBrains Mono',monospace;font-size:.7rem;
  padding:.18rem .55rem;border-radius:6px;
  background:rgba(108,99,255,0.1);color:var(--acc);
  border:1px solid rgba(108,99,255,0.18);
}
.mono{font-family:'JetBrains Mono',monospace}
.low{color:var(--err);font-weight:700}

/* Total box */
.total-box{
  margin-top:1.2rem;padding:1rem 1.2rem;
  background:linear-gradient(135deg,rgba(108,99,255,0.1),rgba(167,139,250,0.12));
  border:1px solid rgba(108,99,255,0.25);border-radius:12px;
  display:flex;align-items:center;justify-content:space-between;
}
.total-box span{font-size:.85rem;color:var(--mut)}
.total-box strong{font-size:1.3rem;color:var(--acc);font-family:'JetBrains Mono',monospace}

/* Vazio */
.empty{text-align:center;padding:2.5rem;color:var(--mut);font-size:.9rem}
.empty .ic{font-size:2.5rem;display:block;margin-bottom:.6rem}

/* Sair */
.bye{text-align:center;padding:3rem}
.bye .ic{font-size:3rem;display:block;margin-bottom:1rem}
.bye h2{font-size:1.35rem;margin-bottom:.5rem}
.bye p{color:var(--mut);font-size:.9rem}
</style>
</head>
<body>
<div class="wrap">

<header>
  <div class="logo">📦</div>
  <div>
    <h1>Sistema de <span>Controle</span><br>de Produtos</h1>
  </div>
  <div class="badge"><strong><?= count($n) ?></strong>/<?= $MAX ?> produtos</div>
</header>

<?php if ($msg): ?>
<div class="msg <?= str_starts_with($msg,'✓') ? 'ok' : 'err' ?>"><?= $msg ?></div>
<?php endif; ?>

<?php // ── MENU ──────────────────────────────────────────────────────────────
if ($acao === 'menu'): ?>
<div class="card">
  <h2><span class="ic">☰</span> Menu Principal</h2>
  <div class="grid2">
    <?php foreach ([
      ['cadastrar','＋','Cadastrar Produto',''],
      ['listar','📋','Listar Produtos',''],
      ['buscar','🔍','Buscar pelo Nome',''],
      ['estoque_baixo','⚠️','Estoque Baixo',''],
      ['valor_total','💰','Valor Total do Estoque',''],
      ['sair','👋','Sair','danger'],
    ] as [$a,$ic,$label,$cls]): ?>
    <form method="post">
      <input type="hidden" name="acao" value="<?= $a ?>">
      <button class="menu-btn <?= $cls ?>" type="submit">
        <span class="num"><?= array_search([$a,$ic,$label,$cls],[['cadastrar','＋','Cadastrar Produto',''],['listar','📋','Listar Produtos',''],['buscar','🔍','Buscar pelo Nome',''],['estoque_baixo','⚠️','Estoque Baixo',''],['valor_total','💰','Valor Total do Estoque',''],['sair','👋','Sair','danger']])+1 ?></span>
        <?= $ic ?> <?= $label ?>
      </button>
    </form>
    <?php endforeach; ?>
  </div>
</div>

<?php // ── CADASTRAR ─────────────────────────────────────────────────────────
elseif ($acao === 'cadastrar'): ?>
<div class="card">
  <h2><span class="ic">＋</span> Cadastrar Produto</h2>
  <?php if (count($n) >= $MAX): ?>
    <div class="empty"><span class="ic">🚫</span>Limite de <?= $MAX ?> produtos atingido.</div>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="acao" value="cadastrar_post">
    <div class="row"><label>Nome do Produto</label>
      <input type="text" name="nome" placeholder="Ex: Notebook Dell" required></div>
    <div class="grid2">
      <div class="row"><label>Categoria</label>
        <select name="categoria" required>
          <option value="">Selecione…</option>
          <?php foreach (['Eletrônicos','Informática','Alimentos','Vestuário','Móveis','Ferramentas','Papelaria','Outros'] as $c): ?>
          <option><?= $c ?></option><?php endforeach; ?>
        </select></div>
      <div class="row"><label>Quantidade</label>
        <input type="number" name="quantidade" min="0" placeholder="0" required></div>
    </div>
    <div class="row"><label>Preço Unitário (R$)</label>
      <input type="text" name="preco" placeholder="0,00" required></div>
    <button class="btn" type="submit">Cadastrar</button>
    <button class="btn-back" type="button"
      onclick="this.form.querySelector('[name=acao]').value='menu';this.form.submit()">← Voltar</button>
  </form>
  <?php endif; ?>
</div>

<?php // ── LISTAR ────────────────────────────────────────────────────────────
elseif ($acao === 'listar'): ?>
<div class="card">
  <h2><span class="ic">📋</span> Produtos Cadastrados</h2>
  <?php if (empty($n)): ?>
    <div class="empty"><span class="ic">📭</span>Nenhum produto cadastrado.</div>
  <?php else: ?>
  <div class="tbl-wrap"><table>
    <thead><tr><th>#</th><th>Nome</th><th>Categoria</th><th>Qtd</th><th>Preço</th><th>Total</th></tr></thead>
    <tbody>
    <?php foreach ($n as $i => $nome): $low = $qtd[$i] < 5; ?>
      <tr>
        <td class="mono"><?= $i+1 ?></td>
        <td><?= htmlspecialchars($nome) ?></td>
        <td><span class="tag"><?= htmlspecialchars($cat[$i]) ?></span></td>
        <td class="mono <?= $low?'low':'' ?>"><?= $qtd[$i] ?><?= $low?' ⚠':'' ?></td>
        <td class="mono"><?= moeda($prc[$i]) ?></td>
        <td class="mono"><?= moeda($qtd[$i]*$prc[$i]) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>

<?php // ── BUSCAR (form) ─────────────────────────────────────────────────────
elseif ($acao === 'buscar'): ?>
<div class="card">
  <h2><span class="ic">🔍</span> Buscar pelo Nome</h2>
  <form method="post">
    <input type="hidden" name="acao" value="buscar_post">
    <div class="row"><label>Nome</label>
      <input type="text" name="busca" placeholder="Digite o nome…" required autofocus></div>
    <button class="btn" type="submit">Buscar</button>
    <button class="btn-back" type="button"
      onclick="this.form.querySelector('[name=acao]').value='menu';this.form.submit()">← Voltar</button>
  </form>
</div>

<?php // ── BUSCAR (resultado) ────────────────────────────────────────────────
elseif ($acao === 'buscar_resultado'):
  $busca = trim($_POST['busca'] ?? '');
  $achou = false;
?>
<div class="card">
  <h2><span class="ic">🔍</span> Resultado: "<?= htmlspecialchars($busca) ?>"</h2>
  <?php foreach ($n as $i => $nome):
    if (strcasecmp($nome, $busca) !== 0) continue;
    $achou = true; ?>
  <div class="tbl-wrap"><table>
    <thead><tr><th>Campo</th><th>Valor</th></tr></thead>
    <tbody>
      <tr><td>Nome</td><td><?= htmlspecialchars($nome) ?></td></tr>
      <tr><td>Categoria</td><td><span class="tag"><?= htmlspecialchars($cat[$i]) ?></span></td></tr>
      <tr><td>Quantidade</td><td class="mono"><?= $qtd[$i] ?></td></tr>
      <tr><td>Preço Unitário</td><td class="mono"><?= moeda($prc[$i]) ?></td></tr>
      <tr><td>Total em Estoque</td><td class="mono"><?= moeda($qtd[$i]*$prc[$i]) ?></td></tr>
    </tbody>
  </table></div>
  <?php endforeach;
  if (!$achou): ?>
    <div class="empty"><span class="ic">❌</span>Produto não encontrado.</div>
  <?php endif; ?>
</div>

<?php // ── ESTOQUE BAIXO ─────────────────────────────────────────────────────
elseif ($acao === 'estoque_baixo'):
  $baixos = array_keys(array_filter($qtd, fn($q) => $q < 5));
?>
<div class="card">
  <h2><span class="ic">⚠️</span> Estoque Baixo (< 5 un.)</h2>
  <?php if (empty($baixos)): ?>
    <div class="empty"><span class="ic">✅</span>Nenhum produto com estoque baixo.</div>
  <?php else: ?>
  <div class="tbl-wrap"><table>
    <thead><tr><th>#</th><th>Nome</th><th>Categoria</th><th>Qtd</th><th>Preço</th></tr></thead>
    <tbody>
    <?php foreach ($baixos as $i): ?>
      <tr>
        <td class="mono"><?= $i+1 ?></td>
        <td><?= htmlspecialchars($n[$i]) ?></td>
        <td><span class="tag"><?= htmlspecialchars($cat[$i]) ?></span></td>
        <td class="mono low"><?= $qtd[$i] ?> ⚠</td>
        <td class="mono"><?= moeda($prc[$i]) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>

<?php // ── VALOR TOTAL ───────────────────────────────────────────────────────
elseif ($acao === 'valor_total'): ?>
<div class="card">
  <h2><span class="ic">💰</span> Valor Total do Estoque</h2>
  <?php if (empty($n)): ?>
    <div class="empty"><span class="ic">📭</span>Nenhum produto cadastrado.</div>
  <?php else:
    $total = 0; ?>
  <div class="tbl-wrap"><table>
    <thead><tr><th>#</th><th>Nome</th><th>Qtd</th><th>Preço</th><th>Total</th></tr></thead>
    <tbody>
    <?php foreach ($n as $i => $nome):
      $sub = $qtd[$i] * $prc[$i]; $total += $sub; ?>
      <tr>
        <td class="mono"><?= $i+1 ?></td>
        <td><?= htmlspecialchars($nome) ?></td>
        <td class="mono"><?= $qtd[$i] ?></td>
        <td class="mono"><?= moeda($prc[$i]) ?></td>
        <td class="mono"><?= moeda($sub) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="total-box"><span>Total Geral</span><strong><?= moeda($total) ?></strong></div>
  <?php endif; ?>
</div>

<?php // ── SAIR ──────────────────────────────────────────────────────────────
elseif ($acao === 'sair'): ?>
<div class="card">
  <div class="bye">
    <span class="ic">👋</span>
    <h2>Sistema encerrado</h2>
    <p>Obrigado por utilizar o Sistema de Controle de Produtos.<br>Todos os dados da sessão foram apagados.</p>
    <br>
    <form method="post">
      <input type="hidden" name="acao" value="menu">
      <button class="btn" type="submit">Reiniciar Sistema</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (!in_array($acao, ['menu','cadastrar','buscar','sair'])): ?>
<form method="post"><input type="hidden" name="acao" value="menu">
  <button class="btn-back" type="submit">← Voltar ao Menu</button>
</form>
<?php endif; ?>

</div>
</body>
</html>