<?php
$page_title = 'Movimientos de Inventario';
require_once 'includes/page_start.php';
$msg = '';
// Cargar productos para el select
$prods_sel = $conexion->query("SELECT id_producto, nombre FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_prod=intval($_POST['id_producto']); $tipo_m=$_POST['tipo_movimiento']; $cant=intval($_POST['cantidad']); $fecha=$_POST['fecha']; $ref=trim($_POST['referencia']);
        $stmt=$conexion->prepare("INSERT INTO movimiento_inventario(id_producto,tipo_movimiento,cantidad,fecha,referencia)VALUES(?,?,?,?,?)");
        $stmt->bind_param("isiss",$id_prod,$tipo_m,$cant,$fecha,$ref); $stmt->execute()?$msg='success|Movimiento registrado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM movimiento_inventario WHERE id_movimiento=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Movimiento eliminado.'; $stmt->close();
    }
}
$movimientos=$conexion->query("SELECT m.*, p.nombre FROM movimiento_inventario m JOIN producto_terminado p ON m.id_producto=p.id_producto ORDER BY m.fecha DESC, m.id_movimiento DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Movimientos de <span>Inventario</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Registrar Movimiento</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-arrows-rotate" style="color:var(--accent2);margin-right:8px;"></i>Historial de Movimientos</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Fecha</th><th>Referencia</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($movimientos&&$movimientos->num_rows>0):while($m=$movimientos->fetch_assoc()):?>
        <tr><td><?=$m['id_movimiento']?></td><td><strong><?=htmlspecialchars($m['nombre'])?></strong></td>
        <td><span class="badge <?=$m['tipo_movimiento']==='ENTRADA'?'badge-active':'badge-orange'?>"><?=$m['tipo_movimiento']?></span></td>
        <td><strong><?=$m['cantidad']?></strong></td><td><?=$m['fecha']?></td><td style="color:var(--muted)"><?=htmlspecialchars($m['referencia']??'—')?></td>
        <td><form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$m['id_movimiento']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td></tr>
        <?php endwhile;else:?><tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-arrows-rotate"></i><p>No hay movimientos</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Registrar Movimiento</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0); while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Tipo *</label><select class="form-control" name="tipo_movimiento" required><option value="ENTRADA">ENTRADA</option><option value="SALIDA">SALIDA</option></select></div>
    <div class="form-group"><label>Cantidad *</label><input class="form-control" name="cantidad" type="number" min="1" required></div>
    <div class="form-group"><label>Fecha *</label><input class="form-control" name="fecha" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"><label>Referencia</label><input class="form-control" name="referencia" placeholder="Factura, orden..."></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
