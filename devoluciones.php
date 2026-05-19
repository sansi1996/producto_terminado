<?php
$page_title = 'Devoluciones';
require_once 'includes/page_start.php';
$msg = '';
$ventas_sel = $conexion->query("SELECT v.id_venta, c.nombre_cliente, v.fecha_venta FROM venta v JOIN cliente c ON v.id_cliente=c.id_cliente ORDER BY v.fecha_venta DESC");
$prods_sel  = $conexion->query("SELECT id_producto, nombre FROM producto_terminado ORDER BY nombre ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_v=intval($_POST['id_venta']); $fd=$_POST['fecha_devolucion']; $motivo=trim($_POST['motivo']);
        $stmt=$conexion->prepare("INSERT INTO devolucion(id_venta,fecha_devolucion,motivo)VALUES(?,?,?)");
        $stmt->bind_param("iss",$id_v,$fd,$motivo);
        if($stmt->execute()){
            $id_dev=$stmt->insert_id; $stmt->close();
            $ids=$_POST['det_producto']??[]; $cants=$_POST['det_cantidad']??[];
            $stmt2=$conexion->prepare("INSERT INTO detalle_devolucion(id_devolucion,id_producto,cantidad)VALUES(?,?,?)");
            foreach($ids as $k=>$pid){if(!$pid) continue; $p=intval($pid); $q=intval($cants[$k]); $stmt2->bind_param("iii",$id_dev,$p,$q); $stmt2->execute();}
            $stmt2->close(); $msg='success|Devolución registrada.';
        } else { $msg='danger|'.$stmt->error; $stmt->close(); }
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $conexion->query("DELETE FROM detalle_devolucion WHERE id_devolucion=$id"); $stmt=$conexion->prepare("DELETE FROM devolucion WHERE id_devolucion=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Devolución eliminada.'; $stmt->close();
    }
}
$devoluciones=$conexion->query("SELECT d.*, c.nombre_cliente, v.fecha_venta FROM devolucion d JOIN venta v ON d.id_venta=v.id_venta JOIN cliente c ON v.id_cliente=c.id_cliente ORDER BY d.fecha_devolucion DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Gestión de <span>Devoluciones</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nueva Devolución</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-rotate-left" style="color:var(--accent2);margin-right:8px;"></i>Registro de Devoluciones</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Cliente</th><th>Venta #</th><th>Fecha Dev.</th><th>Motivo</th><th>Productos</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($devoluciones&&$devoluciones->num_rows>0):while($d=$devoluciones->fetch_assoc()):
            $dets=$conexion->query("SELECT dd.*, p.nombre FROM detalle_devolucion dd JOIN producto_terminado p ON dd.id_producto=p.id_producto WHERE dd.id_devolucion=".$d['id_devolucion']);
        ?>
        <tr><td><?=$d['id_devolucion']?></td><td><strong><?=htmlspecialchars($d['nombre_cliente'])?></strong></td><td>#<?=$d['id_venta']?></td><td><?=$d['fecha_devolucion']?></td><td style="color:var(--muted);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($d['motivo']??'—')?></td>
        <td style="font-size:.78rem;color:var(--muted)"><?php if($dets&&$dets->num_rows>0):while($dt=$dets->fetch_assoc()):?><div><?=htmlspecialchars($dt['nombre'])?>: <?=$dt['cantidad']?></div><?php endwhile;endif;?></td>
        <td><form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$d['id_devolucion']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td></tr>
        <?php endwhile;else:?><tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-rotate-left"></i><p>No hay devoluciones</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal modal-lg"><div class="modal-header"><h3>Nueva Devolución</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2" style="margin-bottom:20px;">
    <div class="form-group"><label>Venta *</label><select class="form-control" name="id_venta" required><option value="">-- Seleccionar --</option><?php $ventas_sel->data_seek(0);while($v=$ventas_sel->fetch_assoc()):?><option value="<?=$v['id_venta']?>">#<?=$v['id_venta']?> — <?=htmlspecialchars($v['nombre_cliente'])?></option><?php endwhile;?></select></div>
    <div class="form-group"><label>Fecha Devolución *</label><input class="form-control" name="fecha_devolucion" type="date" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group" style="grid-column:1/-1"><label>Motivo</label><textarea class="form-control" name="motivo" rows="2"></textarea></div>
</div>
<div style="margin-bottom:12px;font-size:.8rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Productos Devueltos</div>
<div id="detalles">
    <div class="detalle-row form-grid" style="grid-template-columns:2fr 1fr auto;margin-bottom:10px;align-items:end;">
        <div class="form-group" style="margin-bottom:0"><select class="form-control" name="det_producto[]"><option value="">-- Producto --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
        <div class="form-group" style="margin-bottom:0"><input class="form-control" name="det_cantidad[]" type="number" min="1" value="1" placeholder="Cant."></div>
        <button type="button" class="btn btn-danger btn-sm" onclick="remRow(this)"><i class="fa-solid fa-minus"></i></button>
    </div>
</div>
<button type="button" class="btn btn-edit btn-sm" onclick="addRow()" style="margin-bottom:20px;"><i class="fa-solid fa-plus"></i> Agregar Producto</button>
<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar Devolución</button></div>
</form></div></div>
<script>
function addRow(){const r=document.querySelector('.detalle-row').cloneNode(true);r.querySelectorAll('select,input').forEach(el=>{if(el.tagName==='SELECT')el.value='';else el.value=1;});r.querySelector('button').onclick=function(){remRow(this);};document.getElementById('detalles').appendChild(r);}
function remRow(b){const rows=document.querySelectorAll('.detalle-row');if(rows.length>1)b.closest('.detalle-row').remove();}
</script>
<?php require_once 'includes/page_end.php'; ?>
