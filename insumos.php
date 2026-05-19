<?php
$page_title = 'Insumos';
require_once 'includes/page_start.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $n=trim($_POST['nombre_insumo']); $d=trim($_POST['descripcion']); $u=trim($_POST['unidad_medida']); $s=intval($_POST['stock_actual']);
        $stmt=$conexion->prepare("INSERT INTO insumo(nombre_insumo,descripcion,unidad_medida,stock_actual)VALUES(?,?,?,?)");
        $stmt->bind_param("sssi",$n,$d,$u,$s); $stmt->execute()?$msg='success|Insumo agregado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'editar') {
        $id=$_POST['id']; $n=trim($_POST['nombre_insumo']); $d=trim($_POST['descripcion']); $u=trim($_POST['unidad_medida']); $s=intval($_POST['stock_actual']);
        $stmt=$conexion->prepare("UPDATE insumo SET nombre_insumo=?,descripcion=?,unidad_medida=?,stock_actual=? WHERE id_insumo=?");
        $stmt->bind_param("sssii",$n,$d,$u,$s,$id); $stmt->execute()?$msg='success|Insumo actualizado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM insumo WHERE id_insumo=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Insumo eliminado.'; $stmt->close();
    }
}
$insumos=$conexion->query("SELECT * FROM insumo ORDER BY nombre_insumo ASC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Gestión de <span>Insumos</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nuevo Insumo</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-flask-vial" style="color:var(--accent2);margin-right:8px;"></i>Inventario de Insumos</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nombre</th><th>Descripción</th><th>Unidad Medida</th><th>Stock Actual</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($insumos&&$insumos->num_rows>0):while($i=$insumos->fetch_assoc()):?>
        <tr><td><?=$i['id_insumo']?></td><td><strong><?=htmlspecialchars($i['nombre_insumo'])?></strong></td><td style="color:var(--muted)"><?=htmlspecialchars($i['descripcion']??'—')?></td><td><span class="badge badge-blue"><?=htmlspecialchars($i['unidad_medida']??'—')?></span></td><td><span class="badge <?=$i['stock_actual']<=5?'badge-orange':'badge-purple'?>"><?=$i['stock_actual']?></span></td>
        <td><div class="td-actions">
            <button class="btn btn-edit btn-sm" onclick="editarInsumo(<?=htmlspecialchars(json_encode($i))?>)"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$i['id_insumo']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
        </div></td></tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-flask-vial"></i><p>No hay insumos</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3><i class="fa-solid fa-plus" style="color:var(--accent2);margin-right:8px;"></i>Nuevo Insumo</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre_insumo" required></div>
    <div class="form-group" style="grid-column:1/-1"><label>Descripción</label><textarea class="form-control" name="descripcion" rows="2"></textarea></div>
    <div class="form-group"><label>Unidad de Medida</label><input class="form-control" name="unidad_medida" placeholder="kg, litros, unidades..."></div>
    <div class="form-group"><label>Stock Actual *</label><input class="form-control" name="stock_actual" type="number" min="0" value="0" required></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<div class="modal-overlay" id="modalEditar"><div class="modal"><div class="modal-header"><h3><i class="fa-solid fa-pen" style="color:var(--accent2);margin-right:8px;"></i>Editar Insumo</h3><button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" id="e_id"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre_insumo" id="e_nombre" required></div>
    <div class="form-group" style="grid-column:1/-1"><label>Descripción</label><textarea class="form-control" name="descripcion" id="e_desc" rows="2"></textarea></div>
    <div class="form-group"><label>Unidad de Medida</label><input class="form-control" name="unidad_medida" id="e_unidad"></div>
    <div class="form-group"><label>Stock Actual *</label><input class="form-control" name="stock_actual" id="e_stock" type="number" min="0" required></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button></div></form></div></div>
<script>function editarInsumo(i){document.getElementById('e_id').value=i.id_insumo;document.getElementById('e_nombre').value=i.nombre_insumo;document.getElementById('e_desc').value=i.descripcion??'';document.getElementById('e_unidad').value=i.unidad_medida??'';document.getElementById('e_stock').value=i.stock_actual;openModal('modalEditar');}</script>
<?php require_once 'includes/page_end.php'; ?>
