<?php
$page_title = 'Órdenes de Producción';
require_once 'includes/page_start.php';
$msg = '';
$prods_sel = $conexion->query("SELECT id_producto, nombre FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_prod=intval($_POST['id_producto']); $fi=$_POST['fecha_inicio']; $ff=$_POST['fecha_fin']??null; $cant=intval($_POST['cantidad_planificada']); $est=$_POST['estado'];
        $stmt=$conexion->prepare("INSERT INTO orden_produccion(id_producto,fecha_inicio,fecha_fin,cantidad_planificada,estado)VALUES(?,?,?,?,?)");
        $stmt->bind_param("ississ",$id_prod,$fi,$ff,$cant,$est);
        // Note: fix bind
        $stmt=$conexion->prepare("INSERT INTO orden_produccion(id_producto,fecha_inicio,fecha_fin,cantidad_planificada,estado)VALUES(?,?,?,?,?)");
        $stmt->bind_param("isiis",$id_prod,$fi,$cant,$est,$ff);
        if($stmt->execute()){ $msg='success|Orden creada.'; $stmt->close(); } else { $msg='danger|'.$stmt->error; $stmt->close(); }
    } elseif ($accion === 'cambiar_estado') {
        $id=intval($_POST['id']); $est=$_POST['estado'];
        $stmt=$conexion->prepare("UPDATE orden_produccion SET estado=? WHERE id_orden=?"); $stmt->bind_param("si",$est,$id); $stmt->execute(); $msg='success|Estado actualizado.'; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $conexion->query("DELETE FROM detalle_orden WHERE id_orden=$id"); $stmt=$conexion->prepare("DELETE FROM orden_produccion WHERE id_orden=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Orden eliminada.'; $stmt->close();
    }
}
$ordenes=$conexion->query("SELECT o.*, p.nombre FROM orden_produccion o JOIN producto_terminado p ON o.id_producto=p.id_producto ORDER BY o.fecha_inicio DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
$estadoBadge=['PENDIENTE'=>'badge-orange','EN_PROCESO'=>'badge-blue','COMPLETADA'=>'badge-active','CANCELADA'=>'badge-inactive'];
?>
<div class="page-header"><h1 class="page-title">Órdenes de <span>Producción</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nueva Orden</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-industry" style="color:var(--accent2);margin-right:8px;"></i>Lista de Órdenes</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Producto</th><th>Inicio</th><th>Fin Est.</th><th>Cantidad</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($ordenes&&$ordenes->num_rows>0):while($o=$ordenes->fetch_assoc()):?>
        <tr><td><?=$o['id_orden']?></td><td><strong><?=htmlspecialchars($o['nombre'])?></strong></td><td><?=$o['fecha_inicio']?></td><td><?=$o['fecha_fin']??'—'?></td><td><strong><?=$o['cantidad_planificada']?></strong></td>
        <td><span class="badge <?=$estadoBadge[$o['estado']]??'badge-purple'?>"><?=$o['estado']?></span></td>
        <td><div class="td-actions">
            <form method="POST" style="display:flex;gap:4px;align-items:center;">
                <input type="hidden" name="accion" value="cambiar_estado"><input type="hidden" name="id" value="<?=$o['id_orden']?>">
                <select class="form-control" name="estado" style="padding:5px 8px;font-size:0.75rem;height:30px;">
                    <option <?=$o['estado']==='PENDIENTE'?'selected':''?>>PENDIENTE</option>
                    <option <?=$o['estado']==='EN_PROCESO'?'selected':''?>>EN_PROCESO</option>
                    <option <?=$o['estado']==='COMPLETADA'?'selected':''?>>COMPLETADA</option>
                    <option <?=$o['estado']==='CANCELADA'?'selected':''?>>CANCELADA</option>
                </select>
                <button type="submit" class="btn btn-edit btn-sm"><i class="fa-solid fa-check"></i></button>
            </form>
            <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$o['id_orden']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
        </div></td></tr>
        <?php endwhile;else:?><tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-industry"></i><p>No hay órdenes de producción</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Nueva Orden de Producción</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha Inicio *</label><input class="form-control" name="fecha_inicio" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"><label>Fecha Fin Estimada</label><input class="form-control" name="fecha_fin" type="date"></div>
    <div class="form-group"><label>Cantidad Planificada *</label><input class="form-control" name="cantidad_planificada" type="number" min="1" required></div>
    <div class="form-group"><label>Estado</label><select class="form-control" name="estado"><option>PENDIENTE</option><option>EN_PROCESO</option><option>COMPLETADA</option></select></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Crear Orden</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
