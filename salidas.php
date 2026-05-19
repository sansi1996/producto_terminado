<?php
$page_title = 'Salidas de Producto';
require_once 'includes/page_start.php';
$msg = '';
$prods_sel   = $conexion->query("SELECT id_producto, nombre FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");
$dists_sel   = $conexion->query("SELECT id_distribuidor, nombre_empresa FROM distribuidor WHERE estado='ACTIVO' ORDER BY nombre_empresa ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_p=intval($_POST['id_producto']); $id_d=intval($_POST['id_distribuidor']); $fs=$_POST['fecha_salida']; $cant=intval($_POST['cantidad']); $pv=floatval($_POST['precio_venta']??0);
        $stmt=$conexion->prepare("INSERT INTO salida_producto(id_producto,id_distribuidor,fecha_salida,cantidad,precio_venta)VALUES(?,?,?,?,?)");
        $stmt->bind_param("iiisid",$id_p,$id_d,$fs,$cant,$pv);
        $stmt=$conexion->prepare("INSERT INTO salida_producto(id_producto,id_distribuidor,fecha_salida,cantidad,precio_venta)VALUES(?,?,?,?,?)");
        $stmt->bind_param("iisid",$id_p,$id_d,$fs,$cant,$pv);
        $stmt->execute()?$msg='success|Salida registrada.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM salida_producto WHERE id_salida=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Registro eliminado.'; $stmt->close();
    }
}
$salidas=$conexion->query("SELECT s.*, p.nombre AS producto, d.nombre_empresa FROM salida_producto s JOIN producto_terminado p ON s.id_producto=p.id_producto JOIN distribuidor d ON s.id_distribuidor=d.id_distribuidor ORDER BY s.fecha_salida DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Salidas de <span>Producto</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Registrar Salida</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-right-from-bracket" style="color:var(--accent2);margin-right:8px;"></i>Historial de Salidas</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Producto</th><th>Distribuidor</th><th>Fecha Salida</th><th>Cantidad</th><th>Precio Venta</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($salidas&&$salidas->num_rows>0):while($s=$salidas->fetch_assoc()):?>
        <tr><td><?=$s['id_salida']?></td><td><strong><?=htmlspecialchars($s['producto'])?></strong></td><td><?=htmlspecialchars($s['nombre_empresa'])?></td><td><?=$s['fecha_salida']?></td><td><span class="badge badge-orange"><?=$s['cantidad']?></span></td>
        <td style="color:#4ade80"><?=$s['precio_venta']?'$'.number_format($s['precio_venta'],2):'—'?></td>
        <td><form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$s['id_salida']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td></tr>
        <?php endwhile;else:?><tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-right-from-bracket"></i><p>No hay salidas</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Registrar Salida</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Distribuidor *</label><select class="form-control" name="id_distribuidor" required><option value="">-- Seleccionar --</option><?php $dists_sel->data_seek(0);while($d=$dists_sel->fetch_assoc()):?><option value="<?=$d['id_distribuidor']?>"><?=htmlspecialchars($d['nombre_empresa'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha Salida *</label><input class="form-control" name="fecha_salida" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"><label>Cantidad *</label><input class="form-control" name="cantidad" type="number" min="1" required></div>
    <div class="form-group" style="grid-column:1/-1"><label>Precio de Venta</label><input class="form-control" name="precio_venta" type="number" step="0.01" min="0"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
