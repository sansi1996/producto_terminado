<?php
$page_title = 'Alertas de Stock';
require_once 'includes/page_start.php';
$msg = '';
$prods_sel = $conexion->query("SELECT id_producto, nombre, stock_actual FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $id_p=intval($_POST['id_producto']); $min=intval($_POST['stock_minimo']);
        $stmt=$conexion->prepare("REPLACE INTO alerta_stock(id_producto,stock_minimo)VALUES(?,?)");
        $stmt->bind_param("ii",$id_p,$min); $stmt->execute()?$msg='success|Alerta configurada.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM alerta_stock WHERE id_alerta=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Alerta eliminada.'; $stmt->close();
    }
}
$alertas=$conexion->query("SELECT a.*, p.nombre, p.stock_actual FROM alerta_stock a JOIN producto_terminado p ON a.id_producto=p.id_producto ORDER BY p.nombre ASC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Alertas de <span>Stock</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nueva Alerta</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-triangle-exclamation" style="color:var(--accent2);margin-right:8px;"></i>Umbrales de Stock Mínimo</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Producto</th><th>Stock Mínimo</th><th>Stock Actual</th><th>Estado</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($alertas&&$alertas->num_rows>0):while($a=$alertas->fetch_assoc()):
            $bajo=$a['stock_actual']<=$a['stock_minimo'];
        ?>
        <tr style="<?=$bajo?'background:rgba(239,68,68,.04);':''?>">
            <td><?=$a['id_alerta']?></td><td><strong><?=htmlspecialchars($a['nombre'])?></strong></td>
            <td><span class="badge badge-purple"><?=$a['stock_minimo']?></span></td>
            <td><span class="badge <?=$bajo?'badge-inactive':'badge-active'?>"><?=$a['stock_actual']?></span></td>
            <td><?php if($bajo):?><span class="badge badge-inactive"><i class="fa-solid fa-triangle-exclamation"></i> Stock bajo</span><?php else:?><span class="badge badge-active"><i class="fa-solid fa-check"></i> Correcto</span><?php endif;?></td>
            <td><form method="POST" onsubmit="return confirm('¿Eliminar alerta?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$a['id_alerta']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td>
        </tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-triangle-exclamation"></i><p>No hay alertas configuradas</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Configurar Alerta de Stock</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid">
    <div class="form-group"><label>Producto *</label><select class="form-control" name="id_producto" required><option value="">-- Seleccionar --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>"><?=htmlspecialchars($p['nombre'])?> (Stock: <?=$p['stock_actual']?>)</option><?php endwhile;?></select></div>
    <div class="form-group"><label>Stock Mínimo *</label><input class="form-control" name="stock_minimo" type="number" min="0" required placeholder="Alertar cuando stock sea menor o igual a este número..."></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<?php require_once 'includes/page_end.php'; ?>
