<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: index.php"); exit(); }
require_once 'conexion.php';

$nombre = htmlspecialchars($_SESSION['nombre']);
$rol    = htmlspecialchars($_SESSION['rol']);
$hora   = (int) date('H');
if ($hora >= 5 && $hora < 12)      $saludo = 'Buenos días';
elseif ($hora >= 12 && $hora < 18) $saludo = 'Buenas tardes';
else                                $saludo = 'Buenas noches';

// ── Stats reales ──────────────────────────────────────────
$stat_prods    = $conexion->query("SELECT COUNT(*) AS c FROM producto_terminado WHERE estado='ACTIVO'")->fetch_assoc()['c'];
$stat_ventas   = $conexion->query("SELECT COUNT(*) AS c FROM venta WHERE MONTH(fecha_venta)=MONTH(NOW()) AND YEAR(fecha_venta)=YEAR(NOW())")->fetch_assoc()['c'];
$stat_ordenes  = $conexion->query("SELECT COUNT(*) AS c FROM orden_produccion WHERE estado='PENDIENTE'")->fetch_assoc()['c'];
$stat_alertas  = $conexion->query("SELECT COUNT(*) AS c FROM alerta_stock a JOIN producto_terminado p ON a.id_producto=p.id_producto WHERE p.stock_actual <= a.stock_minimo")->fetch_assoc()['c'];
$total_mes_r   = $conexion->query("SELECT COALESCE(SUM(total),0) AS t FROM venta WHERE MONTH(fecha_venta)=MONTH(NOW()) AND YEAR(fecha_venta)=YEAR(NOW())")->fetch_assoc()['t'];
$ultimas_ventas= $conexion->query("SELECT v.id_venta, c.nombre_cliente, v.fecha_venta, v.total FROM venta v JOIN cliente c ON v.id_cliente=c.id_cliente ORDER BY v.fecha_venta DESC LIMIT 5");
$prod_bajo     = $conexion->query("SELECT p.nombre, p.stock_actual, a.stock_minimo FROM alerta_stock a JOIN producto_terminado p ON a.id_producto=p.id_producto WHERE p.stock_actual<=a.stock_minimo ORDER BY p.stock_actual ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Prod. Terminado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="includes/base.css">
    <style>
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:20px; margin-bottom:28px; }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:22px 18px; display:flex; align-items:center; gap:14px; transition:transform .2s, box-shadow .2s; animation:fadeUp .4s ease both; }
        .stat-card:hover { transform:translateY(-4px); box-shadow:0 12px 30px rgba(0,0,0,.3); }
        .stat-icon { width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
        .stat-label { font-size:.76rem; color:var(--muted); font-weight:500; }
        .stat-value { font-size:1.55rem; font-weight:800; margin-top:2px; }
        .section-title { font-size:.95rem; font-weight:700; margin-bottom:14px; display:flex; align-items:center; gap:10px; }
        .section-title::after { content:''; flex:1; height:1px; background:var(--border); }
        .two-col { display:grid; grid-template-columns:1fr 1fr; gap:22px; margin-top:28px; }
        .modules-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(145px,1fr)); gap:14px; margin-bottom:28px; }
        .module-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:24px 16px; text-align:center; cursor:pointer; transition:transform .2s, box-shadow .2s, border-color .2s; text-decoration:none; color:var(--text); animation:fadeUp .4s ease both; }
        .module-card:hover { transform:translateY(-5px); box-shadow:0 14px 35px rgba(0,0,0,.35); border-color:rgba(108,99,255,.35); }
        .module-icon { width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 12px; }
        .module-name { font-size:.84rem; font-weight:600; }
        .c-purple{background:rgba(108,99,255,.18);color:#a78bfa;} .c-teal{background:rgba(20,184,166,.18);color:#2dd4bf;} .c-orange{background:rgba(251,146,60,.18);color:#fb923c;} .c-pink{background:rgba(236,72,153,.18);color:#f472b6;} .c-green{background:rgba(34,197,94,.18);color:#4ade80;} .c-blue{background:rgba(59,130,246,.18);color:#60a5fa;} .c-yellow{background:rgba(234,179,8,.18);color:#facc15;} .c-red{background:rgba(239,68,68,.18);color:#f87171;} .c-cyan{background:rgba(6,182,212,.18);color:#22d3ee;} .c-lime{background:rgba(132,204,22,.18);color:#a3e635;}
        @media(max-width:900px){.two-col{grid-template-columns:1fr;}}
    </style>
</head>
<body>
<?php
$nombre_ses = $nombre;
$rol_ses    = $rol;
$pag_actual = basename($_SERVER['PHP_SELF']);
require_once 'includes/sidebar.php';
?>


<main class="main">
    <!-- Topbar -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
        <div>
            <h2 style="font-size:1.55rem;font-weight:800;background:linear-gradient(135deg,#fff,var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;"><?=$saludo?>, <?=explode(' ',$nombre)[0]?> 👋</h2>
            <p style="color:var(--muted);font-size:.83rem;margin-top:4px;"><span class="badge badge-purple"><?=$rol?></span>&nbsp;Bienvenido al panel de control</p>
        </div>
        <div style="font-size:.8rem;color:var(--muted);background:var(--surface);border:1px solid var(--border);padding:8px 16px;border-radius:20px;">
            <i class="fa-regular fa-calendar" style="margin-right:6px;"></i>
            <?php $dias=['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']; $meses=['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']; echo $dias[date('w')].', '.date('j').' de '.$meses[date('n')-1].' de '.date('Y'); ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card" style="animation-delay:.05s"><div class="stat-icon c-purple"><i class="fa-solid fa-boxes-stacked"></i></div><div><div class="stat-label">Productos activos</div><div class="stat-value"><?=$stat_prods?></div></div></div>
        <div class="stat-card" style="animation-delay:.10s"><div class="stat-icon c-teal"><i class="fa-solid fa-cart-shopping"></i></div><div><div class="stat-label">Ventas del mes</div><div class="stat-value"><?=$stat_ventas?></div></div></div>
        <div class="stat-card" style="animation-delay:.15s"><div class="stat-icon c-orange"><i class="fa-solid fa-industry"></i></div><div><div class="stat-label">Órdenes pendientes</div><div class="stat-value"><?=$stat_ordenes?></div></div></div>
        <div class="stat-card" style="animation-delay:.20s"><div class="stat-icon c-pink"><i class="fa-solid fa-triangle-exclamation"></i></div><div><div class="stat-label">Alertas de stock</div><div class="stat-value" style="color:<?=$stat_alertas>0?'#f87171':'inherit'?>"><?=$stat_alertas?></div></div></div>
        <div class="stat-card" style="animation-delay:.25s;grid-column:span 1;"><div class="stat-icon c-green"><i class="fa-solid fa-dollar-sign"></i></div><div><div class="stat-label">Total ventas mes</div><div class="stat-value" style="font-size:1.1rem;"><?='$'.number_format($total_mes_r,2)?></div></div></div>
    </div>

    <!-- Acceso Rápido -->
    <div class="section-title">Acceso Rápido</div>
    <div class="modules-grid">
        <a class="module-card" href="productos.php" style="animation-delay:.06s"><div class="module-icon c-purple"><i class="fa-solid fa-boxes-stacked"></i></div><div class="module-name">Productos</div></a>
        <a class="module-card" href="insumos.php"   style="animation-delay:.10s"><div class="module-icon c-teal"><i class="fa-solid fa-flask-vial"></i></div><div class="module-name">Insumos</div></a>
        <a class="module-card" href="ordenes.php"   style="animation-delay:.14s"><div class="module-icon c-orange"><i class="fa-solid fa-industry"></i></div><div class="module-name">Órdenes</div></a>
        <a class="module-card" href="ventas.php"    style="animation-delay:.18s"><div class="module-icon c-green"><i class="fa-solid fa-cart-shopping"></i></div><div class="module-name">Ventas</div></a>
        <a class="module-card" href="clientes.php"  style="animation-delay:.22s"><div class="module-icon c-blue"><i class="fa-solid fa-users"></i></div><div class="module-name">Clientes</div></a>
        <a class="module-card" href="distribuidores.php" style="animation-delay:.26s"><div class="module-icon c-yellow"><i class="fa-solid fa-truck"></i></div><div class="module-name">Distribuidores</div></a>
        <a class="module-card" href="pagos.php"     style="animation-delay:.30s"><div class="module-icon c-cyan"><i class="fa-solid fa-credit-card"></i></div><div class="module-name">Pagos</div></a>
        <a class="module-card" href="alertas.php"   style="animation-delay:.34s"><div class="module-icon c-red"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="module-name">Alertas</div></a>
    </div>

    <!-- 2 cols: Últimas ventas + Productos en alerta -->
    <div class="two-col">
        <div class="card"><div class="card-header"><h2><i class="fa-solid fa-cart-shopping" style="color:var(--accent2);margin-right:8px;"></i>Últimas Ventas</h2></div>
        <div class="table-wrap"><table>
            <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th></tr></thead>
            <tbody>
            <?php if($ultimas_ventas&&$ultimas_ventas->num_rows>0):while($v=$ultimas_ventas->fetch_assoc()):?>
            <tr><td>#<?=$v['id_venta']?></td><td><?=htmlspecialchars($v['nombre_cliente'])?></td><td><?=$v['fecha_venta']?></td><td style="color:#4ade80;font-weight:700;">$<?=number_format($v['total'],2)?></td></tr>
            <?php endwhile;else:?><tr><td colspan="4"><div class="empty-state" style="padding:24px;"><i class="fa-solid fa-cart-shopping"></i><p style="font-size:.8rem;">Sin ventas aún</p></div></td></tr><?php endif;?>
            </tbody>
        </table></div></div>

        <div class="card"><div class="card-header"><h2><i class="fa-solid fa-triangle-exclamation" style="color:#f87171;margin-right:8px;"></i>Productos con Stock Bajo</h2></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Producto</th><th>Stock Actual</th><th>Mínimo</th></tr></thead>
            <tbody>
            <?php if($prod_bajo&&$prod_bajo->num_rows>0):while($p=$prod_bajo->fetch_assoc()):?>
            <tr><td><?=htmlspecialchars($p['nombre'])?></td><td><span class="badge badge-inactive"><?=$p['stock_actual']?></span></td><td><span class="badge badge-orange"><?=$p['stock_minimo']?></span></td></tr>
            <?php endwhile;else:?><tr><td colspan="3"><div class="empty-state" style="padding:24px;"><i class="fa-solid fa-check" style="color:#4ade80;"></i><p style="font-size:.8rem;">Todo el stock está correcto</p></div></td></tr><?php endif;?>
            </tbody>
        </table></div></div>
    </div>
</main>
</body></html>
