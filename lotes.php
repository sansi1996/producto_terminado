<?php
$page_title = 'Lotes de Producción';
require_once 'includes/page_start.php';
$msg = '';
$prods_sel = $conexion->query("SELECT id_producto, nombre FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_prod=intval($_POST['id_producto']); $fp=$_POST['fecha_produccion']; $fv=$_POST['fecha_vencimiento']??null; $cant=intval($_POST['cantidad_producida']);
        $stmt=$conexion->prepare("INSERT INTO lote_produccion(id_producto,fecha_produccion,fecha_vencimiento,cantidad_producida)VALUES(?,?,?,?)");
        $stmt->bind_param("issi",$id_prod,$fp,$fv,$cant); $stmt->execute()?$msg='success|Lote registrado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'editar') {
        $id=intval($_POST['id']); $id_prod=intval($_POST['id_producto']); $fp=$_POST['fecha_produccion']; $fv=$_POST['fecha_vencimiento']??null; $cant=intval($_POST['cantidad_producida']);
        $stmt=$conexion->prepare("UPDATE lote_produccion SET id_producto=?,fecha_produccion=?,fecha_vencimiento=?,cantidad_producida=? WHERE id_lote=?");
        $stmt->bind_param("issii",$id_prod,$fp,$fv,$cant,$id); $stmt->execute()?$msg='success|Lote actualizado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM lote_produccion WHERE id_lote=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Lote eliminado.'; $stmt->close();
    }
}
$lotes=$conexion->query("SELECT l.*, p.nombre FROM lote_produccion l JOIN producto_terminado p ON l.id_producto=p.id_producto ORDER BY l.fecha_produccion DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Lotes de <span>Producción</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nuevo Lote</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-layer-group" style="color:var(--accent2);margin-right:8px;"></i>Registro de Lotes</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Producto</th><th>Fecha Producción</th><th>Fecha Vencimiento</th><th>Cantidad</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($lotes&&$lotes->num_rows>0):while($l=$lotes->fetch_assoc()):
            $venc=$l['fecha_vencimiento']; $hoy=date('Y-m-d'); $badge='badge-active'; if($venc&&$venc<$hoy) $badge='badge-inactive'; elseif($venc&&$venc<=date('Y-m-d',strtotime('+30 days'))) $badge='badge-orange';
        ?>
        <tr><td><?=$l['id_lote']?></td><td><strong><?=htmlspecialchars($l['nombre'])?></strong></td><td><?=$l['fecha_produccion']?></td>
        <td><span class="badge <?=$badge?>"><?=$venc??'Sin fecha'?></span></td><td><strong><?=$l['cantidad_producida']?></strong></td>
        <td><div class="td-actions">
            <button class="btn btn-edit btn-sm" onclick="editLote(<?=htmlspecialchars(json_encode($l))?>)"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$l['id_lote']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
        </div></td></tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-layer-group"></i><p>No hay lotes</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Nuevo Lote</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha Producción *</label><input class="form-control" name="fecha_produccion" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"><label>Fecha Vencimiento</label><input class="form-control" name="fecha_vencimiento" type="date"></div>
    <div class="form-group"><label>Cantidad Producida *</label><input class="form-control" name="cantidad_producida" type="number" min="1" required></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<div class="modal-overlay" id="modalEditar"><div class="modal"><div class="modal-header"><h3>Editar Lote</h3><button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" id="e_id"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Producto *</label><select class="form-control" name="id_producto" id="e_prod" required><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha Producción *</label><input class="form-control" name="fecha_produccion" id="e_fp" type="date" required></div>
    <div class="form-group"><label>Fecha Vencimiento</label><input class="form-control" name="fecha_vencimiento" id="e_fv" type="date"></div>
    <div class="form-group"><label>Cantidad Producida *</label><input class="form-control" name="cantidad_producida" id="e_cant" type="number" min="1" required></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button></div></form></div></div>
<script>function editLote(l){document.getElementById('e_id').value=l.id_lote;document.getElementById('e_prod').value=l.id_producto;document.getElementById('e_fp').value=l.fecha_produccion;document.getElementById('e_fv').value=l.fecha_vencimiento??'';document.getElementById('e_cant').value=l.cantidad_producida;openModal('modalEditar');}</script>
<?php require_once 'includes/page_end.php'; ?>
