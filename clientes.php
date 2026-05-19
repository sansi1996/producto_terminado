<?php
$page_title = 'Clientes';
require_once 'includes/page_start.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $n=trim($_POST['nombre_cliente']); $t=trim($_POST['telefono']); $e=trim($_POST['email']); $d=trim($_POST['direccion']);
        $stmt=$conexion->prepare("INSERT INTO cliente(nombre_cliente,telefono,email,direccion)VALUES(?,?,?,?)");
        $stmt->bind_param("ssss",$n,$t,$e,$d); $stmt->execute()?$msg='success|Cliente agregado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'editar') {
        $id=$_POST['id']; $n=trim($_POST['nombre_cliente']); $t=trim($_POST['telefono']); $e=trim($_POST['email']); $d=trim($_POST['direccion']);
        $stmt=$conexion->prepare("UPDATE cliente SET nombre_cliente=?,telefono=?,email=?,direccion=? WHERE id_cliente=?");
        $stmt->bind_param("ssssi",$n,$t,$e,$d,$id); $stmt->execute()?$msg='success|Cliente actualizado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM cliente WHERE id_cliente=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Cliente eliminado.'; $stmt->close();
    }
}
$clientes=$conexion->query("SELECT * FROM cliente ORDER BY nombre_cliente ASC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Gestión de <span>Clientes</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nuevo Cliente</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-users" style="color:var(--accent2);margin-right:8px;"></i>Directorio de Clientes</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Dirección</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($clientes&&$clientes->num_rows>0):while($c=$clientes->fetch_assoc()):?>
        <tr><td><?=$c['id_cliente']?></td><td><strong><?=htmlspecialchars($c['nombre_cliente'])?></strong></td><td><?=htmlspecialchars($c['telefono']??'—')?></td><td style="color:var(--accent2)"><?=htmlspecialchars($c['email']??'—')?></td><td style="color:var(--muted)"><?=htmlspecialchars($c['direccion']??'—')?></td>
        <td><div class="td-actions">
            <button class="btn btn-edit btn-sm" onclick="editarCliente(<?=htmlspecialchars(json_encode($c))?>)"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$c['id_cliente']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
        </div></td></tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-users"></i><p>No hay clientes</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3><i class="fa-solid fa-plus" style="color:var(--accent2);margin-right:8px;"></i>Nuevo Cliente</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre_cliente" required></div>
    <div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono"></div>
    <div class="form-group"><label>Email</label><input class="form-control" name="email" type="email"></div>
    <div class="form-group" style="grid-column:1/-1"><label>Dirección</label><input class="form-control" name="direccion"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<div class="modal-overlay" id="modalEditar"><div class="modal"><div class="modal-header"><h3><i class="fa-solid fa-pen" style="color:var(--accent2);margin-right:8px;"></i>Editar Cliente</h3><button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" id="e_id"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre_cliente" id="e_nombre" required></div>
    <div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono" id="e_tel"></div>
    <div class="form-group"><label>Email</label><input class="form-control" name="email" id="e_email" type="email"></div>
    <div class="form-group" style="grid-column:1/-1"><label>Dirección</label><input class="form-control" name="direccion" id="e_dir"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button></div></form></div></div>
<script>function editarCliente(c){document.getElementById('e_id').value=c.id_cliente;document.getElementById('e_nombre').value=c.nombre_cliente;document.getElementById('e_tel').value=c.telefono??'';document.getElementById('e_email').value=c.email??'';document.getElementById('e_dir').value=c.direccion??'';openModal('modalEditar');}</script>
<?php require_once 'includes/page_end.php'; ?>
