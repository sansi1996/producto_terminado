<?php
$page_title = 'Auditoría de Inventario';
require_once 'includes/page_start.php';
$msg = '';
$prods_sel = $conexion->query("SELECT id_producto, nombre FROM producto_terminado ORDER BY nombre ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion']??'') === 'agregar') {
    $id_p=intval($_POST['id_producto']); $fecha=$_POST['fecha']; $sa=intval($_POST['stock_anterior']); $sc=intval($_POST['stock_actual']); $dif=$sc-$sa;
    $stmt=$conexion->prepare("INSERT INTO auditoria_inventario(id_producto,fecha,stock_anterior,stock_actual,diferencia)VALUES(?,?,?,?,?)");
    $stmt->bind_param("isiii",$id_p,$fecha,$sa,$sc,$dif); $stmt->execute()?$msg='success|Auditoría registrada.':$msg='danger|'.$stmt->error; $stmt->close();
}
$auditorias=$conexion->query("SELECT a.*, p.nombre FROM auditoria_inventario a JOIN producto_terminado p ON a.id_producto=p.id_producto ORDER BY a.fecha DESC, a.id_auditoria DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Auditoría de <span>Inventario</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nueva Auditoría</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-clipboard-list" style="color:var(--accent2);margin-right:8px;"></i>Historial de Auditorías</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Producto</th><th>Fecha</th><th>Stock Anterior</th><th>Stock Actual</th><th>Diferencia</th></tr></thead>
        <tbody>
        <?php if($auditorias&&$auditorias->num_rows>0):while($a=$auditorias->fetch_assoc()):$dif=$a['diferencia'];?>
        <tr><td><?=$a['id_auditoria']?></td><td><strong><?=htmlspecialchars($a['nombre'])?></strong></td><td><?=$a['fecha']?></td>
        <td><span class="badge badge-purple"><?=$a['stock_anterior']?></span></td>
        <td><span class="badge badge-blue"><?=$a['stock_actual']?></span></td>
        <td><span class="badge <?=$dif>0?'badge-active':($dif<0?'badge-inactive':'badge-purple')?>"><?=$dif>0?'+'.$dif:$dif?></span></td></tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-clipboard-list"></i><p>No hay registros de auditoría</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Registrar Auditoría</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha *</label><input class="form-control" name="fecha" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"></div>
    <div class="form-group"><label>Stock Anterior *</label><input class="form-control" name="stock_anterior" type="number" min="0" required></div>
    <div class="form-group"><label>Stock Actual (Contado) *</label><input class="form-control" name="stock_actual" type="number" min="0" required></div>
    <div class="form-group" style="grid-column:1/-1"><p style="font-size:.8rem;color:var(--muted);">La diferencia se calcula automáticamente.</p></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
