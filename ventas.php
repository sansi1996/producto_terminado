<?php
$page_title = 'Ventas';
require_once 'includes/page_start.php';
$msg = '';
$clientes_sel = $conexion->query("SELECT id_cliente, nombre_cliente FROM cliente ORDER BY nombre_cliente ASC");
$prods_sel    = $conexion->query("SELECT id_producto, nombre, precio_unitario FROM producto_terminado WHERE estado='ACTIVO' ORDER BY nombre ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion']??'') === 'agregar') {
    $id_cli  = intval($_POST['id_cliente']);
    $fecha   = $_POST['fecha_venta'];
    $total   = floatval($_POST['total']);
    $stmt = $conexion->prepare("INSERT INTO venta(id_cliente,fecha_venta,total)VALUES(?,?,?)");
    $stmt->bind_param("isd",$id_cli,$fecha,$total);
    if ($stmt->execute()) {
        $id_venta = $stmt->insert_id;
        $stmt->close();
        // detalles
        $ids    = $_POST['det_producto'] ?? [];
        $cants  = $_POST['det_cantidad']  ?? [];
        $prices = $_POST['det_precio']    ?? [];
        $stmt2 = $conexion->prepare("INSERT INTO detalle_venta(id_venta,id_producto,cantidad,precio_unitario)VALUES(?,?,?,?)");
        foreach($ids as $k=>$pid){
            if(!$pid) continue;
            $p=intval($pid); $q=intval($cants[$k]); $pr=floatval($prices[$k]);
            $stmt2->bind_param("iiid",$id_venta,$p,$q,$pr); $stmt2->execute();
        }
        $stmt2->close();
        $msg = 'success|Venta registrada correctamente.';
    } else { $msg='danger|'.$stmt->error; $stmt->close(); }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion']??'') === 'eliminar') {
    $id=intval($_POST['id']);
    $conexion->query("DELETE FROM detalle_venta WHERE id_venta=$id");
    $stmt=$conexion->prepare("DELETE FROM venta WHERE id_venta=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Venta eliminada.'; $stmt->close();
}
$ventas = $conexion->query("SELECT v.*, c.nombre_cliente FROM venta v JOIN cliente c ON v.id_cliente=c.id_cliente ORDER BY v.fecha_venta DESC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Registro de <span>Ventas</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nueva Venta</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-cart-shopping" style="color:var(--accent2);margin-right:8px;"></i>Historial de Ventas</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Detalles</th><th>Acc.</th></tr></thead>
        <tbody>
        <?php if($ventas&&$ventas->num_rows>0):while($v=$ventas->fetch_assoc()):
            $dets=$conexion->query("SELECT dv.*, p.nombre FROM detalle_venta dv JOIN producto_terminado p ON dv.id_producto=p.id_producto WHERE dv.id_venta=".$v['id_venta']);
        ?>
        <tr>
            <td><?=$v['id_venta']?></td><td><strong><?=htmlspecialchars($v['nombre_cliente'])?></strong></td><td><?=$v['fecha_venta']?></td>
            <td style="color:#4ade80;font-weight:700;">$<?=number_format($v['total'],2)?></td>
            <td style="font-size:0.78rem;color:var(--muted)"><?php if($dets&&$dets->num_rows>0):while($d=$dets->fetch_assoc()):?><div><?=htmlspecialchars($d['nombre'])?>: <?=$d['cantidad']?> x $<?=number_format($d['precio_unitario'],2)?></div><?php endwhile;endif;?></td>
            <td><form method="POST" onsubmit="return confirm('¿Eliminar venta?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$v['id_venta']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form></td>
        </tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-cart-shopping"></i><p>No hay ventas registradas</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<!-- MODAL NUEVA VENTA -->
<div class="modal-overlay" id="modalAgregar"><div class="modal modal-lg">
    <div class="modal-header"><h3><i class="fa-solid fa-cart-plus" style="color:var(--accent2);margin-right:8px;"></i>Nueva Venta</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
    <form method="POST" id="ventaForm"><input type="hidden" name="accion" value="agregar">
        <div class="form-grid form-grid-2" style="margin-bottom:20px;">
            <div class="form-group"><label>Cliente *</label><select class="form-control" name="id_cliente" required><option value="">-- Seleccionar --</option><?php $clientes_sel->data_seek(0);while($c=$clientes_sel->fetch_assoc()):?><option value="<?=$c['id_cliente']?>"><?=htmlspecialchars($c['nombre_cliente'])?></option><?php endwhile;?></select></div>
            <div class="form-group"><label>Fecha *</label><input class="form-control" name="fecha_venta" type="date" value="<?=date('Y-m-d')?>" required></div>
        </div>
        <div style="margin-bottom:12px;font-size:0.8rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Productos</div>
        <div id="detalles">
            <div class="detalle-row form-grid form-grid-2" style="margin-bottom:10px;grid-template-columns:2fr 1fr 1fr auto;align-items:end;">
                <div class="form-group" style="margin-bottom:0"><select class="form-control prod-sel" name="det_producto[]" onchange="setPrecio(this)"><option value="">-- Producto --</option><?php $prods_sel->data_seek(0);while($p=$prods_sel->fetch_assoc()):?><option value="<?=$p['id_producto']?>" data-precio="<?=$p['precio_unitario']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile;?></select></div>
                <div class="form-group" style="margin-bottom:0"><input class="form-control cant-input" name="det_cantidad[]" type="number" min="1" value="1" placeholder="Cant." onchange="recalcTotal()"></div>
                <div class="form-group" style="margin-bottom:0"><input class="form-control precio-input" name="det_precio[]" type="number" step="0.01" min="0" placeholder="Precio" onchange="recalcTotal()"></div>
                <button type="button" class="btn btn-danger btn-sm" onclick="remRow(this)"><i class="fa-solid fa-minus"></i></button>
            </div>
        </div>
        <button type="button" class="btn btn-edit btn-sm" onclick="addRow()" style="margin-bottom:20px;"><i class="fa-solid fa-plus"></i> Agregar Producto</button>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
            <div style="font-size:1rem;font-weight:700;">Total: <span id="totalDisplay" style="color:#4ade80;">$0.00</span></div>
            <input type="hidden" name="total" id="totalInput" value="0">
            <div style="display:flex;gap:10px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Venta</button></div>
        </div>
    </form>
</div></div>
<script>
const prodData = {};
<?php $prods_sel->data_seek(0); while($p=$prods_sel->fetch_assoc()):?>
prodData[<?=$p['id_producto']?>] = <?=$p['precio_unitario']?>;
<?php endwhile;?>
function setPrecio(sel){const precio=prodData[sel.value]||0;sel.closest('.detalle-row').querySelector('.precio-input').value=precio;recalcTotal();}
function recalcTotal(){let t=0;document.querySelectorAll('.detalle-row').forEach(r=>{const q=parseInt(r.querySelector('.cant-input').value)||0;const p=parseFloat(r.querySelector('.precio-input').value)||0;t+=q*p;});document.getElementById('totalDisplay').textContent='$'+t.toFixed(2);document.getElementById('totalInput').value=t.toFixed(2);}
function addRow(){
    const template=document.querySelector('.detalle-row').cloneNode(true);
    template.querySelectorAll('select,input').forEach(el=>{if(el.tagName==='SELECT')el.value=''; else if(el.type==='number'&&el.classList.contains('cant-input')) el.value=1; else el.value='';});
    template.querySelector('.prod-sel').onchange=function(){setPrecio(this);};
    template.querySelector('.cant-input').onchange=recalcTotal;
    template.querySelector('.precio-input').onchange=recalcTotal;
    template.querySelector('button').onclick=function(){remRow(this);};
    document.getElementById('detalles').appendChild(template);
}
function remRow(btn){const rows=document.querySelectorAll('.detalle-row');if(rows.length>1){btn.closest('.detalle-row').remove();recalcTotal();}}
</script>
<?php require_once 'includes/page_end.php'; ?>
