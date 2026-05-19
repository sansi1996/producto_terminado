<?php
$page_title = 'Pagos';
require_once 'includes/page_start.php';
$msg = '';
$ventas_sel = $conexion->query("SELECT v.id_venta, c.nombre_cliente, v.fecha_venta, v.total FROM venta v JOIN cliente c ON v.id_cliente=c.id_cliente ORDER BY v.fecha_venta DESC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_v=intval($_POST['id_venta']); $fp=$_POST['fecha_pago']; $monto=floatval($_POST['monto']); $metodo=trim($_POST['metodo_pago']);
        $stmt=$conexion->prepare("INSERT INTO pago(id_venta,fecha_pago,monto,metodo_pago)VALUES(?,?,?,?)");
        $stmt->bind_param("isds",$id_v,$fp,$monto,$metodo); $stmt->execute()?$msg='success|Pago registrado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM pago WHERE id_pago=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Pago eliminado.'; $stmt->close();
    }
}
$pagos=$conexion->query("SELECT p.*, c.nombre_cliente, v.total AS total_venta FROM pago p JOIN venta v ON p.id_venta=v.id_venta JOIN cliente c ON v.id_cliente=c.id_cliente ORDER BY p.fecha_pago DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
$metodoBadge=['Efectivo'=>'badge-active','Transferencia'=>'badge-blue','Cheque'=>'badge-purple','Tarjeta'=>'badge-orange'];
?>
<div class="page-header"><h1 class="page-title">Registro de <span>Pagos</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Registrar Pago</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-credit-card" style="color:var(--accent2);margin-right:8px;"></i>Historial de Pagos</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Cliente</th><th>Venta #</th><th>Fecha Pago</th><th>Monto</th><th>Método</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($pagos&&$pagos->num_rows>0):while($p=$pagos->fetch_assoc()):?>
        <tr><td><?=$p['id_pago']?></td><td><strong><?=htmlspecialchars($p['nombre_cliente'])?></strong></td><td>#<?=$p['id_venta']?> <span style="color:var(--muted);font-size:.78rem;">(Total: $<?=number_format($p['total_venta'],2)?>)</span></td>
        <td><?=$p['fecha_pago']?></td><td style="color:#4ade80;font-weight:700;">$<?=number_format($p['monto'],2)?></td>
        <td><span class="badge <?=$metodoBadge[$p['metodo_pago']]??"badge-purple"?>"><?=htmlspecialchars($p['metodo_pago']??'—')?></span></td>
        <td><form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$p['id_pago']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td></tr>
        <?php endwhile;else:?><tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-credit-card"></i><p>No hay pagos</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Registrar Pago</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Venta *</label><select class="form-control" name="id_venta" required><option value="">-- Seleccionar Venta --</option><?php $ventas_sel->data_seek(0);while($v=$ventas_sel->fetch_assoc()):?><option value="<?=$v['id_venta']?>">#<?=$v['id_venta']?> — <?=htmlspecialchars($v['nombre_cliente'])?> | $<?=number_format($v['total'],2)?> | <?=$v['fecha_venta']?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha Pago *</label><input class="form-control" name="fecha_pago" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"><label>Monto *</label><input class="form-control" name="monto" type="number" step="0.01" min="0" required></div>
    <div class="form-group" style="grid-column:1/-1"><label>Método de Pago</label><select class="form-control" name="metodo_pago"><option>Efectivo</option><option>Transferencia</option><option>Cheque</option><option>Tarjeta</option></select></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
