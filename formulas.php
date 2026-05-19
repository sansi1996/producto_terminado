<?php
$page_title = 'Fórmulas de Producto';
require_once 'includes/page_start.php';
$msg = '';
$prods_sel  = $conexion->query("SELECT id_producto, nombre FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");
$insumos_sel= $conexion->query("SELECT id_insumo, nombre_insumo, unidad_medida FROM insumo ORDER BY nombre_insumo ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_p=intval($_POST['id_producto']); $id_i=intval($_POST['id_insumo']); $cant=floatval($_POST['cantidad_requerida']);
        $stmt=$conexion->prepare("INSERT INTO formula_producto(id_producto,id_insumo,cantidad_requerida)VALUES(?,?,?) ON DUPLICATE KEY UPDATE cantidad_requerida=?");
        $stmt->bind_param("iidd",$id_p,$id_i,$cant,$cant); $stmt->execute()?$msg='success|Fórmula guardada.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id_p=intval($_POST['id_producto']); $id_i=intval($_POST['id_insumo']);
        $stmt=$conexion->prepare("DELETE FROM formula_producto WHERE id_producto=? AND id_insumo=?"); $stmt->bind_param("ii",$id_p,$id_i); $stmt->execute(); $msg='success|Elemento eliminado.'; $stmt->close();
    }
}
$formulas=$conexion->query("SELECT f.*, p.nombre AS producto, i.nombre_insumo, i.unidad_medida FROM formula_producto f JOIN producto_terminado p ON f.id_producto=p.id_producto JOIN insumo i ON f.id_insumo=i.id_insumo ORDER BY p.nombre, i.nombre_insumo");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Fórmulas de <span>Producto</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Agregar Componente</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-sitemap" style="color:var(--accent2);margin-right:8px;"></i>Composición por Producto</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Producto</th><th>Insumo</th><th>Unidad</th><th>Cantidad Req.</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($formulas&&$formulas->num_rows>0):while($f=$formulas->fetch_assoc()):?>
        <tr><td><strong><?=htmlspecialchars($f['producto'])?></strong></td><td><?=htmlspecialchars($f['nombre_insumo'])?></td><td><span class="badge badge-blue"><?=htmlspecialchars($f['unidad_medida']??'u')?></span></td><td><?=$f['cantidad_requerida']?></td>
        <td><form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id_producto" value="<?=$f['id_producto']?>"><input type="hidden" name="id_insumo" value="<?=$f['id_insumo']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td></tr>
        <?php endwhile;else:?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-sitemap"></i><p>No hay fórmulas configuradas</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Agregar Componente a Fórmula</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid">
    <div class="form-group"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Insumo *</label><select class="form-control" name="id_insumo" required><option value="">-- Seleccionar --</option><?php $insumos_sel->data_seek(0);while($i=$insumos_sel->fetch_assoc()):?><option value="<?=$i['id_insumo']?>"><?=htmlspecialchars($i['nombre_insumo'])?> (<?=htmlspecialchars($i['unidad_medida']??'u')?>)</option><?php endwhile;?></select></div>
    <div class="form-group"><label>Cantidad Requerida *</label><input class="form-control" name="cantidad_requerida" type="number" step="0.01" min="0.01" required placeholder="Cantidad por unidad producida"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
